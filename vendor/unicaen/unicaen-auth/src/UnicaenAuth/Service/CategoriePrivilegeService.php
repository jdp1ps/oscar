<?php

namespace UnicaenAuth\Service;

use UnicaenAuth\Entity\Db\CategoriePrivilege;


class CategoriePrivilegeService extends AbstractService
{

    /**
     * @return CategoriePrivilege[]
     */
    public function getCategoriesPrivileges()
    {
        $dql        = 'SELECT cp FROM UnicaenAuth\Entity\Db\CategoriePrivilege cp ORDER BY cp.ordre';
        $query      = $this->getEntityManager()->createQuery($dql);
        $categories = $query->getResult();

        return $categories;
    }

}
