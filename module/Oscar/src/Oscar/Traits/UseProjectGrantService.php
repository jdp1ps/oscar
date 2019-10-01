<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 01/10/19
 * Time: 16:39
 */

namespace Oscar\Traits;


use Oscar\Service\ProjectGrantService;

interface UseProjectGrantService
{
    /**
     * @param PersonService $em
     */
    public function setProjectGrantService( ProjectGrantService $s ) :void;

    /**
     * @return PersonService
     */
    public function getProjectGrantService() :ProjectGrantService ;
}