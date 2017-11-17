<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 31/08/17
 * Time: 13:55
 */

namespace Oscar\Connector;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Factory\JsonToOrganization;

class ConnectorOrganizationJSON implements ConnectorInterface
{
    private $jsonDatas;
    private $organizationJsonFactory;
    private $entityManager;
    private $connectorName;

    /**
     * ConnectorAuthentificationJSON constructor.
     * @param array $jsonData
     * @param EntityManager $entityManager
     */
    public function __construct( array $jsonData, EntityManager $entityManager, $connectorName = 'json' )
    {
        $this->jsonDatas = $jsonData;
        $this->entityManager = $entityManager;
        $this->organizationJsonFactory = new JsonToOrganization();
        $this->connectorName = $connectorName;
    }

    protected function checkData( $data ){
        return true;
    }

    /**
     * @param $uid
     * @return Organization
     */
    protected function getOrganization( $uid ){
        /** @var OrganizationRepository $repo */
        $repo = $this->entityManager->getRepository(Organization::class);

        return $repo->getObjectByConnectorID($this->connectorName, $uid);
    }

    public function syncAll()
    {
        $repport = new ConnectorRepport();
        foreach ($this->jsonDatas as $data) {
            if( !property_exists($data, 'uid') ){
                $repport->adderror("Les données sans UID sont ignorées : " . print_r($data, true));
                continue;
            }
            $this->checkData($data);
            try {
                $organization = $this->getOrganization($data->uid);
                $action = 'Mise à jour';
            } catch (NoResultException $e) {
                $action = 'Création';
                $organization = new Organization();
                $this->entityManager->persist($organization);
            }

            try {
                $this->organizationJsonFactory->hydrateWithDatas($organization, $data, $this->connectorName);
            } catch (\Exception $e ){
                $repport->adderror($e->getMessage());
                continue;
            }


            try {
                $this->entityManager->flush($organization);
                $message = sprintf('%s de %s', $action, $organization);
                if( $action == 'Création' ){
                    $repport->addadded($message);
                } else {
                    $repport->addupdated($message);
                }
            }
            catch( \Exception $e ){
                $repport->adderror($message . " a échoué : " . $e->getMessage());
            }
        }
        return $repport;
    }

    public function syncOne($key)
    {
        // TODO: Implement syncOne() method.
    }
}