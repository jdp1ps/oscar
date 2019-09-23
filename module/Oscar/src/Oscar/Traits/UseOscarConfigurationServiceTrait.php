<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;


use Oscar\Service\OscarConfigurationService;

trait UseOscarConfigurationServiceTrait
{
    /**
     * @var OscarConfigurationService
     */
    private $oscarConfigurationService;

    /**
     * @param OscarConfigurationService $s
     */
    public function setOscarConfigurationService( OscarConfigurationService $oscarConfigurationService ) :void
    {
        $this->oscarConfigurationService = $oscarConfigurationService;
    }

    /**
     * @return OscarConfigurationService
     */
    public function getOscarConfigurationService() :OscarConfigurationService {
        return $this->oscarConfigurationService;
    }
}