<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 30/09/15 14:07
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;


use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHtmlElement;

class Access extends AbstractHtmlElement implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    function __invoke( $requireAccess, $granted=null )
    {
        if( $granted == null )
            return false;

        return in_array($requireAccess, $granted);
    }
}