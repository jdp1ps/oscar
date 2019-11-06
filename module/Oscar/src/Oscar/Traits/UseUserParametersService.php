<?php
namespace Oscar\Traits;

use Oscar\Service\UserParametersService;

interface UseUserParametersService
{
    /**
     * @param EntityManager $em
     */
    public function setUserParametersService( UserParametersService $em ) :void;

    /**
     * @return Entitymanager
     */
    public function getUserParametersService() :UserParametersService ;
}