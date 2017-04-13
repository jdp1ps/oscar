<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 02/03/16
 * Time: 16:18
 */

namespace Oscar\View\Helpers;


use Oscar\Service\OscarUserContext;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHtmlElement;

class HasPrivilege extends AbstractHtmlElement implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    function __invoke( $privilege )
    {
        /** @var OscarUserContext $s */
        $s = $this->getServiceLocator()->getServiceLocator()->get('OscarUserContext');

        return $s->hasPrivileges($privilege);
    }
}