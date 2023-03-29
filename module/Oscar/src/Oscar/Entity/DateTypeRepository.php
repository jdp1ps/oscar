<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-12 09:49
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class DateTypeRepository extends EntityRepository
{
    public function allArray()
    {
        try {
            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('id', 'id');
            $rsm->addScalarResult('label', 'label');
            $rsm->addScalarResult('description', 'description');
            $rsm->addScalarResult('recursivity', 'recursivity');
            $rsm->addScalarResult('finishable', 'finishable');
            $rsm->addScalarResult('facet', 'facet');
            $rsm->addScalarResult('roles', 'roles');
            $rsm->addScalarResult('used', 'used');
            $sql = 'select d.id, d.label, d.description, 
                        d.finishable , d.recursivity, d.facet, 
                        count(a.id) as used, json_agg(distinct ur.role_id) as roles from datetype d
                    left join activitydate a ON a.type_id = d.id
                    left join role_datetype rd on rd.datetype_id = d.id 
                    left join user_role ur on ur.id = rd.role_id 
                    group by d.id
                    order by facet;';
            $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
            $results = $query->getResult();

            foreach ($results as &$row) {
                if ($row['roles'] == '[null]') {
                    $row['roles'] = [];
                } else {
                    $rolesStr = $row['roles'];
                    $row['roles'] = json_decode($rolesStr, false);
                }
            }
            return $results;
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}