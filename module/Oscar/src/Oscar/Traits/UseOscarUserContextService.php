<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:59
 */

namespace Oscar\Traits;



use Oscar\Service\OscarUserContext;

interface UseOscarUserContextService
{
    /**
     * @param OscarUserContext $oscarUserContextService
     */
    public function setOscarUserContextService( OscarUserContext $oscarUserContextService ) :void;

    /**
     * @return OscarUserContext
     */
    public function getOscarUserContextService() :OscarUserContext ;
}