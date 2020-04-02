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
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;

class ConnectorPersonJSON implements ConnectorInterface
{
    private $jsonDatas;
    private $connectorPersonHydrator;
    private $entityManager;

    /**
     * ConnectorAuthentificationJSON constructor.
     * @param array $jsonData
     * @param EntityManager $entityManager
     */
    public function __construct( array $jsonData, EntityManager $entityManager )
    {
        $this->jsonDatas = $jsonData;
        $this->entityManager = $entityManager;
        $this->connectorPersonHydrator = new ConnectorPersonHydrator($entityManager);
    }

    protected function checkData( $data ){
        return true;
    }

    protected function getPerson( $uid ){
        /** @var PersonRepository $personRepo */
        $personRepo = $this->entityManager->getRepository(Person::class);

        /** @var Person $personRepo */
        return $personRepo->getPersonByConnectorID('json', $uid);
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
                $person = $this->getPerson($data->uid);
                $action = 'Mise à jour';
            } catch (NoResultException $e) {
                //echo $e->getMessage();
                $action = 'Création';
                $person = new Person();
                $this->entityManager->persist($person);
            }

            $this->connectorPersonHydrator->hydratePerson($person, $data, 'json');
            $repport->addRepport($this->connectorPersonHydrator->getRepport());

            if( $this->connectorPersonHydrator->isSuspect() ){
                $repport->addwarning("Données suspectes pour $person");

                continue;
            }

            try {
                $this->entityManager->flush($person);
                $message = sprintf('%s de %s', $action, $person);
                if( $action == 'Création' ){
                    $repport->addadded($message);
                } else {
                    $repport->addupdated($message);
                }
            }
            catch( \Exception $e ){
                echo $e->getMessage();
                $repport->adderror($message . " a échoué : " . $e->getMessage());
            }
        }
        // $this->entityManager->flush();
        return $repport;
    }

    public function syncOne($key)
    {
        // TODO: Implement syncOne() method.
    }
}