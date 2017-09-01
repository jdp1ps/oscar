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
use Oscar\Entity\Activity;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;

class ConnectorActivityJSON implements ConnectorInterface
{
    private $jsonDatas;
    private $entityManager;


    public function __construct( array $jsonData, EntityManager $entityManager )
    {
        $this->jsonDatas = $jsonData;
        $this->entityManager = $entityManager;
    }

    protected function checkData( $data ){
        return true;
    }

    /**
     * @param $uid
     */
    protected function getActivity( $uid ){

    }

    public function syncAll()
    {
        $repport = new ConnectorRepport();
        foreach ($this->jsonDatas as $data) {
            $this->checkData($data);
            try {
                $activity = $this->getActivity($data->uid);
                $action = 'Mise à jour';
            } catch (NoResultException $e) {
                $action = 'Création';
                $activity = new Activity();
                $this->entityManager->persist($activity);
            }

            $activity->setCentaureId($data->uid)
                ->setLabel($data->label)
                ->setDateStart($data->datestart)
                ->setDateEnd($data->dateend)
                ->setCodeEOTP($data->pfi)
                ->setDateSigned($data->datesigned)
                ->setAmount($data->amount)
                ;

            try {
                // $this->entityManager->flush($activity);
                $message = sprintf('%s de %s', $action, $activity);
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