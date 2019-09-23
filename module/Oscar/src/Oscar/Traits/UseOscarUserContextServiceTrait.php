<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\OscarUserContext;

trait UseOscarUserContextServiceTrait
{
    /**
     * @var OscarUserContext
     */
    private $oscarUserContextService;

    /**
     * @param OscarUserContext $s
     */
    public function setOscarUserContextService( OscarUserContext $oscarUserContextService ) :void
    {
        $this->oscarUserContextService = $oscarUserContextService;
    }

    /**
     * @return OscarUserContext
     */
    public function getOscarUserContextService() :OscarUserContext {
        return $this->oscarUserContextService;
    }
}