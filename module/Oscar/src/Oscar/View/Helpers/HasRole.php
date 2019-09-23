<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 02/03/16
 * Time: 16:18
 */

namespace Oscar\View\Helpers;


use Oscar\Service\OscarUserContext;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHtmlElement;

class HasRole extends AbstractHtmlElement implements UseOscarUserContextService
{
    use UseOscarUserContextServiceTrait;

    function __invoke( $role )
    {
        return $this->getOscarUserContextService()->hasRole($role);
    }
}