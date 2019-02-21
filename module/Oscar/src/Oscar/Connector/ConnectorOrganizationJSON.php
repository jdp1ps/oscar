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
use Oscar\Entity\OrganizationType;
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
    public function __construct( $jsonData, $entityManager, $connectorName = 'json' )
    {
        $this->jsonDatas = $jsonData;
        $this->entityManager = $entityManager;
        $this->organizationJsonFactory = new JsonToOrganization();
        $this->connectorName = $connectorName;
    }

    protected function checkData( $data ){
        return true;
    }

    /** Retourne le format JSON attendu par la classe JsonToOrganization.
     *
     * @return mixed
     */
    public function getJsonData(){
        return $this->jsonDatas;
    }

    public function getEntityManager(){
        return $this->entityManager;
    }

    public function getConnectorName(){
        return $this->connectorName;
    }

    /**
     * @param $uid
     * @return Organization
     */
    protected function getOrganization( $uid ){
        /** @var OrganizationRepository $repo */
        $repo = $this->getEntityManager()->getRepository(Organization::class);

        return $repo->getObjectByConnectorID($this->connectorName, $uid);
    }

    protected function getOrganizationType( $label ){
        return $this->getEntityManager()->getRepository(OrganizationType::class)->findOneBy(['label' => $label]);
    }

    public function syncAll()
    {
        $repport = new ConnectorRepport();
        foreach ($this->getJsonData() as $data) {
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
                $this->getEntityManager()->persist($organization);
            }

            try {
                $this->organizationJsonFactory->hydrateWithDatas($organization, $data, $this->connectorName);
                if( property_exists($data, 'type') && $data->type ){
                    $type = $this->getOrganizationType($data->type);
                    if( $type ){
                        $organization->setTypeObj($type);
                    }
                }
            } catch (\Exception $e ){
                $repport->adderror($e->getMessage());
                continue;
            }


            try {
                $this->getEntityManager()->flush($organization);
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