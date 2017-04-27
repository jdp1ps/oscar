<?php
namespace UnicaenApp\View\Helper\Navigation;

use Zend\View\Helper\Navigation\Breadcrumbs;

/**
 * Aide de vue générant le code HTML du "fil d'Ariane" (breadcrumbs) de l'application.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class FilAriane extends Breadcrumbs
{
    /**
     * Constructeur.
     */
    public function __construct()
    {
        $this->setRenderInvisible(true)
             ->setMinDepth(0);
    }
    
    /**
     * Renders helper
     *
     * Implements {@link HelperInterface::render()}.
     *
     * @param  AbstractContainer $container [optional] container to render. Default is
     *                              to render the container registered in the helper.
     * @return string               helper output
     */
    public function render($container = null)
    {
        $html = parent::render($container);
        $html = sprintf('<ul class="breadcrumb"><li>%s</li></ul>', $html);
        
        return $html;
    }
}