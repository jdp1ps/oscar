<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 22/01/16 10:45
 * @copyright Certic (c) 2016
 */

namespace Oscar\Import\Organization;


use Doctrine\ORM\EntityManager;
use Oscar\Entity\Organization;
use Oscar\Import\ImportInterface;
use UnicaenApp\Mapper\Ldap\Structure;
use Zend\Mvc\Controller\Plugin\Url;


class ImportOrganizationLdapStrategy implements ImportInterface
{
    /** @var  Structure */
    private $ldapStructureService;

    /** @var EntityManager  */
    private $entityManager;

    /** @var  Url */
    private $router;

    const LDAP_FILTER_ALL = '*';

    function __construct( Structure $ldapStructureService, EntityManager $em, Url $router )
    {
        $this->ldapStructureService = $ldapStructureService;
        $this->entityManager = $em;
        $this->router = $router;
    }

    /**
     * Test la différence entre les données LDAP et les données de l'entité
     * Oscar, les réaffectent si besoin.
     *
     * @param array $ldapData
     * @param Organization $organization
     * @return boolean True Si des données ont changée
     */
    function synchronize( array $ldapData, Organization &$organization ){

    }

    function importAll($options = null)
    {
        $report = [
            'errors' => [],
            'created' => [],
            'updated' => [],
        ];

        $datas = $this->ldapStructureService->findAllByCodeStructure(self::LDAP_FILTER_ALL);

        $queryFindByCode = $this->entityManager->getRepository(Organization::class)
            ->createQueryBuilder('o')
            ->where('o.code IN (:code)')
            ->orWhere('o.shortName = :ou');

        /** @var \UnicaenApp\Entity\Ldap\Structure $data */
        foreach( $datas as $data ){

            $split = explode('$', $data->getPostaladdress());

            $code = [$data->getSupannCodeEntite(), substr($data->getSupannCodeEntite(), 3)];

            $ldapData = [
                'shortName'  => $data->getOu(),
                'fullName'  => $data->getDescription(),
                'phone'  => $data->getTelephoneNumber(),
                'ldapSupannCodeEntite'  => $data->getSupannCodeEntite(),
                'country'   => 'country',
                'street1'   => $split[0],
                'street2'   => $split[1],
                'street3'   => $split[2],
                'zipCode'   => $split[3],
                'city'   => $split[4],
                'country'   => $split[5],
                'code'  => str_replace('HS_', '', $data->getSupannCodeEntite()),
            ];

            if( preg_match('/\[(.*)\]/', $data->getDescription(), $matches) ){
                $code[] = $matches[1];
            }

            $search = $queryFindByCode->setParameters([
                'code' => $code,
                'ou' => $data->getOu()
            ]);

            $founded = $search->getQuery()->getResult();

            if( count($founded) === 1){
                $f = $founded[0];

                $changed = false;

                foreach( $ldapData as $method=>$value ){
                    $m = 'get'. ucfirst($method);
                    $set = 'set'. ucfirst($method);
                    if( $f->$m() !== $value ){
                        $f->$set($value);
                        $changed = true;
                    }
                }

                if( $changed ){
                    $f->setDateUpdated(new \DateTime());
                    $this->entityManager->flush($f);
                    $report['updated'][] = $f->log() . " a été mis à jour.";
                }

            } elseif( count($founded) > 1 ){
                $double = [];
                $ids = [];
                /** @var Organization $found */
                foreach( $founded as $found ){
                    $double[] = $found->log();
                    $ids[] = $found->getId();
                }
                $report['errors'][] = "Plusieurs organisations partagent le code "
                    . implode(', ', $code) ." : "
                    . implode(', ', $double)
                    . '<a class="btn btn-xs btn-default" href="' . $this->router->fromRoute('organization/merge') . '?ids=' . implode(',', $ids) . '">Fusionner</a>';
            }

            else {
                $created = new Organization();
                $this->entityManager->persist($created);
                foreach( $ldapData as $method=>$value ){
                    $set = 'set'. ucfirst($method);
                    $created->$set($value);
                }
                $this->entityManager->flush($created);
                $report['created'][] = "L'organisation " . $created->log() . " a été ajoutée dans Oscar.";
            }
        }
        return $report;
    }

    function importOne($object, $options = null)
    {
        // TODO: Implement importOne() method.
    }

    private $_cacheOrganizationsOscar;
    /**
     * @param bool|true $cachAll
     */
    protected function getOrganizationByCode( $code, $cachAll = true )
    {
        return $this->entityManager->getRepository(Organization::class)->findOneBy(['code' => $code]);
    }
}