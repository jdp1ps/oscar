<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 02/03/16
 * Time: 16:18
 */

namespace Oscar\View\Helpers;


use Oscar\Service\ConfigurationParser;
use Oscar\Service\OscarConfigurationService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHtmlElement;

class Options extends AbstractHtmlElement implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @return OscarConfigurationService
     */
    private function getConfiguration()
    {
        return $this->getServiceLocator()->getServiceLocator()->get('OscarConfig');
    }

    public function theme(){
        return $this->getConfiguration()->getTheme();
    }

    public function oscarNumSeparator(){
        return $this->getConfiguration()->getConfiguration('oscar_num_separator');
    }

    public function importEnable(){
        return $this->getConfiguration()->getConfiguration('importEnable');
    }

}
