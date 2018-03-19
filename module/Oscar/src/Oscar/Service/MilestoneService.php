<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 19/03/18
 * Time: 13:16
 */

namespace Oscar\Service;


use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\ActivityPayment;
use Oscar\Provider\Privileges;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class MilestoneService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    public function getMilestonesByActivityId( $idActivity ){
        // Droit d'accès
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);

        return $this->getMiletonesByActivity($activity);
    }

    public function getMiletonesByActivity( Activity $activity ){
        /** @var OscarUserContext $oscarUserContext */
        $oscarUserContext = $this->getServiceLocator()->get('OscarUserContext');

        // Check générale du la visibilité
        $oscarUserContext->check(Privileges::ACTIVITY_MILESTONE_SHOW, $activity);

        // Droits plus précis à transmettre aux objets
        $deletable = $editable = $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_MANAGE, $activity);
        $progression =  $oscarUserContext->hasPrivileges(Privileges::ACTIVITY_MILESTONE_PROGRESSION, $activity);

        $qb = $this->getEntityManager()->getRepository(ActivityDate::class)->createQueryBuilder('d')
            ->addSelect('t')
            ->innerJoin('d.activity', 'a')
            ->innerJoin('d.type', 't')
            ->where('a.id = :idactivity')
            ->orderBy('d.dateStart');

        $dates = $qb->setParameter('idactivity', $activity->getId())->getQuery()->getResult(Query::HYDRATE_ARRAY);

        $out = [];
        $now = new \DateTime();
        foreach( $dates as $data ){
            $data['deletable'] = true;
            $data['past'] = ($data['dateStart']<$now);
            $data['css'] = ($data['dateStart']<$now) ? 'past' : '';
            $data['deletable'] = $deletable;
            $data['editable'] = $editable;
            $data['validable'] = $progression;
            $data['isPayment'] = false;

            $out[$data['dateStart']->format('YmdHis').$data['id']] = $data;
        }

        //  versements sous la forme JALON
        $versementsQB = $this->getEntityManager()->getRepository(ActivityPayment::class)->createQueryBuilder('p')
            ->addSelect('p')
            ->innerJoin('p.activity', 'a')
            ->where('p.status = :status')
            ->andWhere('a.id = :idactivity');

        $versements = $versementsQB->setParameters([
            'idactivity' => $activity->getId(),
            'status' => ActivityPayment::STATUS_PREVISIONNEL
        ])->getQuery()->getResult();

        ksort($out, SORT_STRING);

        return $out;
    }

}