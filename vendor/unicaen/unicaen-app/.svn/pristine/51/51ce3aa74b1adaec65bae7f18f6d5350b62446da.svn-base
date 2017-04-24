<?php
namespace UnicaenApp\View\Helper\Navigation;

use Zend\View\Helper\Navigation\Menu;

/**
 * Aide de vue dessinant le menu de navigation principal (2 premiers niveaux).
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MenuPrincipal extends Menu
{
    /**
     * The minimum depth a page must have to be included when rendering
     *
     * @var int
     */
    protected $minDepth = 1; // zappe la page 'home' qui chapeaute toutes les autres

    /**
     * The maximum depth a page can have to be included when rendering
     *
     * @var int
     */
    protected $maxDepth = 1; // un seul niveau

    /**
     * CSS class to use for the ul element
     *
     * @var string
     */
    protected $ulClass = 'nav navbar-nav menu-principal';
    
}