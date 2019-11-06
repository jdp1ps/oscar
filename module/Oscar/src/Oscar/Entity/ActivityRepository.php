<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 28/08/18
 * Time: 09:15
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class ActivityRepository
 * @package Oscar\Entity
 */
class ActivityRepository extends EntityRepository
{
    /**
     * Retourne la liste des IDS des activités impliquant l'organisation (avec un rôle principal).
     *
     * @param $idOrganization
     */
    public function getActivitiesIdsForOrganizations( $idsOrganization, $principal=true ){
        $query = $this->createQueryBuilder('a')
            ->select('a.id')
            ->innerJoin('a.organizations', 'oa')
            ->innerJoin('oa.roleObj', 'oar')
            ->where('oa.organization IN(:idsOrganization) AND oar.principal = :principal');

        $parameters = [
            'idsOrganization' => $idsOrganization,
            'principal' => $principal
        ];

        return array_map('current', $query->setParameters($parameters)
            ->getQuery()
            ->getResult());
    }

    public function getActivitiesPersonDate(int $personId, \DateTime $date){

        // TODO Revoir la méthode pour la date de fin

        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.persons', 'ap')
            ->leftJoin('a.project', 'p')
            ->innerJoin('p.members', 'pp')
            ->where('(ap.person = :personId  OR pp.person = :personId) AND (a.dateStart < :date AND a.dateEnd > :date)')
            ->setParameters([
                'personId' => $personId,
                'date' => $date
            ])
            ;
        $result  = $qb->getQuery()->getResult();
        return $result;
    }
}