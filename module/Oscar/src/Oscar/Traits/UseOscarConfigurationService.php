<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:59
 */

namespace Oscar\Traits;


use Oscar\Service\OscarConfigurationService;

interface UseOscarConfigurationService
{
    /**
     * @param OscarConfigurationService $s
     */
    public function setOscarConfigurationService( OscarConfigurationService $oscarConfigurationService ) :void;

    /**
     * @return OscarConfigurationService
     */
    public function getOscarConfigurationService() :OscarConfigurationService ;
}