<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 03/10/19
 * Time: 15:04
 */

namespace Oscar\Command\Config;


use Oscar\Command\IConsoleCommand;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\PersonService;

class TestCommand implements IConsoleCommand
{
    public function getParameters(): array
    {
        return [];
    }

    public function getOptions(): array
    {
        return [];
    }


    /** @var OscarConfigurationService  */
    private $oscarConfigurationService;

    /** @var PersonService */
    private $personService;

    /**
     * TestCommand constructor.
     * @param OscarConfigurationService $oscarConfigurationService
     */
    public function __construct(OscarConfigurationService $oscarConfigurationService, PersonService $personService)
    {
        $this->oscarConfigurationService = $oscarConfigurationService;
        $this->personService = $personService;
    }

    /**
     * @return OscarConfigurationService
     */
    protected function getOscarConfigurationService(){
        return $this->oscarConfigurationService;
    }



    public function execute( $parameters = null ){
        echo $this->getOscarConfigurationService()->getTheme();
        var_dump($this->personService);
        die(" - YEAR !");
    }
}