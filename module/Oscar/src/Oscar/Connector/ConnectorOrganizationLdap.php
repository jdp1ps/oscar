<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-23 15:42
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use UnicaenApp\Mapper\Ldap\Structure;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConnectorOrganizationLdap implements IConnectorOrganization, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    /** @var  Structure */
    private $ldapStructureService;

    const LDAP_FILTER_ALL = '*';

    public function __construct(){}


    function getOrganizationData($idConnector)
    {
        // TODO: Implement getOrganizationData() method.
    }

    function syncOrganizations(OrganizationRepository $repository, $force)
    {
        $this->ldapStructureService = $this->getServiceLocator()->get('ldap_structure_service')->getMapper();

        $report = [];

        $datas = $this->ldapStructureService->findAllByCodeStructure(self::LDAP_FILTER_ALL);
        $correspondancesType = $this->getServiceLocator()->get('Config')['oscar']['connectors']['config'];

        $test = [];
        /** @var \UnicaenApp\Entity\Ldap\Structure $data */
        foreach( $datas as $data ){

            $type = str_replace('{SUPANN}', '', $data->getSupannTypeEntite());
            if( array_key_exists($type, $correspondancesType) ){
                $type = $correspondancesType[$type];
            } else {
                $type = "";
            }

            $split = explode('$', $data->getPostaladdress());

            $code = substr($data->getSupannCodeEntite(), 3);

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
                'type' => $type
            ];

            /** @var Organization $organization */
            $organization = null;
            $type = "notice";

            try {
                $organization = $repository->getObjectByConnectorID($this->getName(), $code);
                $organization->setDateUpdated(new \DateTime());
                $type = "update";
            } catch( NoResultException $ex ){
                try {
                    $organization = $repository->getOrganisationByCode($ldapData['code']);
                    $type = "update";
                } catch( NoResultException $ex ){

                } catch( NonUniqueResultException $ex ){
                    $report[] = [
                        'type' => 'error',
                        'message' => 'Doublon détecté sur le code LDAP ' . $ldapData['code']
                    ];
                    continue;
                }
            } catch( NonUniqueResultException $ex ){
                $report[] = [
                    'type' => 'error',
                    'message' => 'Doublon détecté sur le ConnectorID ' . $code
                ];
                continue;
            }

            if( $organization == null ){
                $organization = $repository->newPersistantObject();
                $organization->setDateCreated(new \DateTime());
                $type = "add";
            }

            foreach( $ldapData as $method=>$value ){
                $set = 'set'. ucfirst($method);
                $organization->$set($value);
            }

            $organization->setConnectorID($this->getName(), $data->getSupannCodeEntite());

            $repository->flush($organization);
            $report[] = [
                'type' => $type,
                'message' => ($type == 'update' ? 'Mise à jour de ' : 'Création de ') . (string)$organization
            ];
        }

        return $report;
    }

    function getName() {
        return 'ldap';
    }

    private function getUsedType( $typeLdap ){
        static $correspondancesType;
        if( $correspondancesType === null ){
            $correspondancesType = $this->getServiceLocator()->get('Config')['oscar']['connectors']['config'];
        }

        return array_key_exists($typeLdap, $correspondancesType) ? $correspondancesType[$typeLdap] : null;
    }

    function syncOrganization(Organization $organization)
    {
        $report = [];

        /////////////////////////////////////////////// Récupération des données
        /** @var Structure ldapStructureService */
        $this->ldapStructureService = $this->getServiceLocator()->get('ldap_structure_service')->getMapper();

        $codeLDAP = $organization->getConnectorID($this->getName());
        $data = $this->ldapStructureService->findOneByDnOrCodeEntite($codeLDAP);
        if( !$data ){
            throw new \Exception(sprintf("Impossible d'obtenir les données LDAP pour l'organisation %s", $codeLDAP));
        }


        return $this->hydrateOragnisationWithLdapDatas($organization, $data);
    }

    protected function hydrateOragnisationWithLdapDatas( Organization $organization, $data ){
        $split = explode('$', $data->getPostaladdress());
        $type = str_replace('{SUPANN}', '', $data->getSupannTypeEntite());
        $typeOscar = $this->getUsedType($type);

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
            'type' => $typeOscar
        ];

        foreach( $ldapData as $method=>$value ){
            $set = 'set'. ucfirst($method);
            $organization->$set($value);
        }

        return $organization;
    }
}