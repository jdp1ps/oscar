<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:59
 */

namespace Oscar\Traits;

use Doctrine\ORM\EntityManager;

interface UseEntityManager
{
    /**
     * @param EntityManager $em
     */
    public function setEntityManager( Entitymanager $em ) :void;

    /**
     * @return Entitymanager
     */
    public function getEntityManager() :EntityManager ;
}