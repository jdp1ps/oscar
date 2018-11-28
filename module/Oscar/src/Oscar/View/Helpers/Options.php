<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 02/03/16
 * Time: 16:18
 */

namespace Oscar\View\Helpers;


use Oscar\Service\ConfigurationParser;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHtmlElement;

class Options extends AbstractHtmlElement implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @return ConfigurationParser
     */
    private function getConfiguration()
    {
        return $this->getServiceLocator()->getServiceLocator()->get('OscarConfig');
    }

    public function theme(){
        return $this->getConfiguration()->getConfiguration('theme');
    }

    public function oscarNumSeparator(){
        return $this->getConfiguration()->getConfiguration('oscar_num_separator');
    }

}
