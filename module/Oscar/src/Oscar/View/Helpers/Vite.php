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
use Oscar\Utils\ViteUtils;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHtmlElement;

class Vite extends AbstractHtmlElement implements UseOscarConfigurationService
{
    use UseOscarConfigurationServiceTrait;

    private $viteutils;

    /**
     * Vite constructor.
     * @param ViteUtils $viteutils
     */
    public function __construct()
    {

    }


    /**
     * @return OscarConfigurationService
     */
    private function getConfiguration()
    {
        return $this->getOscarConfigurationService();
    }

    /**
     * @return ViteUtils
     */
    private function getViteUtils() :ViteUtils
    {
        if( $this->viteutils === null ){
            $config = $this->getOscarConfigurationService()->getConfiguration('vite');
            $mode = $config['mode'];
            $pathRoot = $config['dest'];
            $baseUrl = $mode == 'dev' ? $config['base_url_dev'] : $config['base_url_prod'];
            $this->viteutils = new ViteUtils($mode, $pathRoot, $baseUrl);
        }
        return $this->viteutils;
    }

    public function addJs( $file ) :void
    {
        $this->getViteUtils()->build($file);
    }

}
