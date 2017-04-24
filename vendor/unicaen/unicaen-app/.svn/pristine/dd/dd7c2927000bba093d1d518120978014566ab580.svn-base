<?php
namespace UnicaenApp\View\Helper\Navigation;

use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Navigation;
use Zend\View\Helper\Navigation\Menu;

/**
 * Aide de vue générant le menu de pied de page.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MenuPiedDePage extends Menu
{    
    /**
     * Renders menu
     *
     * @param  AbstractContainer $container [optional] container to render. Default is
     *                              to render the container registered in the helper.
     * @return string
     */
    public function render($container = null)
    {
        $this->parseContainer($container);
        if (null === $container) {
            $container = $this->getContainer();
        }

        $pages = $container->findAllBy('footer', true);
        
        $container = new Navigation($pages);
        
        $this//->setContainer($container)
             ->setRenderInvisible(true);

        return Menu::renderMenu($container);
    }
}