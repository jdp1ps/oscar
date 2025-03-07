<?php

namespace Oscar\Entity;

use Doctrine\ORM\EntityRepository;

class OrganizationTypeRepository extends EntityRepository
{
    public function getCountedTypes()
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT 
                    t.id AS id, 
                    COUNT(o.id) AS count
                FROM Oscar\Entity\OrganizationType t 
                LEFT JOIN Oscar\Entity\Organization o WITH t.id = o.typeObj
                GROUP BY t.id
                ORDER BY t.label ASC 
                "
        );
        $out = [];
        foreach ($query->getResult() as $row) {
            $out[$row['id']] = $row['count'];
        }

        return $out;
    }
}
