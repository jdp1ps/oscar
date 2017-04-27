<?php
namespace UnicaenApp\View\Helper\Navigation;

use Zend\Navigation\Page\AbstractPage;
use Zend\View\Helper\Navigation\Menu;

/**
 * Dessine le plan de navigation du site.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Plan extends Menu
{
    /**
     * CSS class to use for the ul element
     *
     * @var string
     */
    protected $ulClass = 'menu-footer';

    /**
     * @see Menu
     */
    public function accept(AbstractPage $page, $recursive = true)
    {
        $accept = parent::accept($page, $recursive);
        
        if (!$accept) {
            // en mode 'recursif', si la page mère n'est pas acceptée, alors la fille non plus
            if ($recursive && ($parent = $page->getParent()) && !parent::accept($parent, $recursive)) {
                return false;
            }
            // accepte les pages qui ont la propriété 'sitemap' à 1
            return (bool)$page->get('sitemap');
        }

        return $accept;
    }
}