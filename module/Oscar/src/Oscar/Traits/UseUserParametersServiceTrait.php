<?php
namespace Oscar\Traits;

use Oscar\Service\UserParametersService;

trait UseUserParametersServiceTrait
{

    /**
     * @var UserParametersService
     */
    private $userParametersService;

    /**
     * @param UserParametersService $typeDocumentService
     */
    public function setUserParametersService( UserParametersService $userParametersService ) :void
    {
        $this->userParametersService = $userParametersService;
    }

    /**
     * @return UserParametersService
     */
    public function getUserParametersService() :UserParametersService {
        return $this->userParametersService;
    }
}