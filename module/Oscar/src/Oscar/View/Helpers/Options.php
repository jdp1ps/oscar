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
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHtmlElement;

class Options extends AbstractHtmlElement implements UseOscarConfigurationService
{
    use UseOscarConfigurationServiceTrait;

    /**
     * @return OscarConfigurationService
     */
    public function getConfiguration()
    {
        return $this->getOscarConfigurationService();
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

    public function hasTimesheetPreview(){
        return $this->getConfiguration()->getTimesheetPreview();
    }

    public function allowTimesheetExcel(){
        return $this->getConfiguration()->getTimesheetExcel();
    }

}
