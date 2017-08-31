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
/*
 * return $personOscar->setConnectorID($this->getName(), $personData->uid)
            ->setLadapLogin($personData->login)
            ->setFirstname($personData->firstname)
            ->setLastname($personData->lastname)
            ->setEmail($personData->mail)
            ->setHarpegeINM($personData->inm)
            ->setPhone($personData->phone)
            ->setDateSyncLdap(new \DateTime())
            ->setLdapStatus($personData->status)
            ->setLdapAffectation($personData->affectation)
            ->setLdapSiteLocation($personData->structure)
            ->setLdapMemberOf($personData->groups);
 */
    private $jsonDatas;
    private $entityManager;
    private $bcrypt;

    /**
     * ConnectorAuthentificationJSON constructor.
     * @param array $jsonData
     * @param EntityManager $entityManager
     */
    public function __construct( array $jsonData, EntityManager $entityManager )
    {
        $this->jsonDatas = $jsonData;
        $this->entityManager = $entityManager;
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
            $this->checkData($data);
            try {
                $person = $this->getPerson($data->uid);
                $action = 'Mise à jour';
            } catch (NoResultException $e) {
                $action = 'Création';
                $person = new Person();
                $this->entityManager->persist($person);
            }

            $person->setConnectorID('json', $data->uid)
                ->setFirstname($data->firstname)
                ->setLastname($data->lastname)
                ->setLadapLogin($data->login)
                ->setEmail($data->email);


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