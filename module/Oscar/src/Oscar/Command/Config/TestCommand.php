<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 03/10/19
 * Time: 15:04
 */

namespace Oscar\Command\Config;


use Oscar\Service\OscarConfigurationService;

class TestCommand
{
    /** @var OscarConfigurationService  */
    private $oscarConfigurationService;

    /**
     * TestCommand constructor.
     * @param OscarConfigurationService $oscarConfigurationService
     */
    public function __construct(OscarConfigurationService $oscarConfigurationService)
    {
        $this->oscarConfigurationService = $oscarConfigurationService;
    }

    /**
     * @return OscarConfigurationService
     */
    protected function getOscarConfigurationService(){
        return $this->oscarConfigurationService;
    }

    public function execute( $parameters = null ){

    }
}