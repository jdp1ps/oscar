<?php
namespace UnicaenApp\View\Helper\Navigation;

use RecursiveIteratorIterator;
use Zend\Navigation\AbstractContainer;

/**
 * Dessine le menu secondaire de l'application (vertical).
 * 
 * Au préalable, mettons-nous d'accord sur niveaux et profondeurs : 
 *    "Accueil" (niv 1, depth 0) > "Deux roues" (niv 2, depth 1) > "Vélos" (niv 3, depth 2) > "VTT" (niv 4, depth 3)
 * 
 * Les règles suivantes sont appliquées :
 * 
 * - Seules les pages visibles sont prises en compte.
 * 
 * - Si la page active est de niveau 1 (accueil), le menu est vide 
 *   (NB: les pages de niveau 2 apparaissent dans le menu principal).
 * 
 * - Si la page active est de niveau 2, seules ses pages filles éventuelles (de niveau 3)
 *   apparaissent dans le menu.
 * 
 * - Si la page active est de niveau N supérieur à 3, elle continue d'apparaître dans le menu, et
 *   ses pages filles (niveau N+1) éventuelles apparaissent dessous.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MenuSecondaire extends AbstractMenu
{
    /**
     * CSS class to use for the ul element
     *
     * @var string
     */
    protected $ulClass = 'nav nav-pills nav-stacked menu-secondaire';

    /**
     * Renders helper
     *
     * Renders a HTML 'ul' for the given $container. If $container is not given,
     * the container registered in the helper will be used.
     *
     * Available $options:
     *
     *
     * @param  AbstractContainer $container [optional] container to create menu from.
     *                                      Default is to use the container retrieved
     *                                      from {@link getContainer()}.
     * @param  array             $options   [optional] options for controlling rendering
     * @return string
     */
    public function renderMenu($container = null, array $options = array())
    {
        $this->parseContainer($container);
        if (null === $container) {
            $container = $this->getContainer();
        }

        $options = $this->normalizeOptions($options);
        
//        echo PHP_EOL;
//        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);
//        foreach ($iterator as $page) { /* @var $page \Zend\Navigation\Page\Mvc */
//            echo str_repeat('  ', $iterator->getDepth()) . sprintf("%s) %s <%s> %s" . PHP_EOL, 
//                    $iterator->getDepth(),
//                    $page->get('route'),
//                    $page->get('label'),
//                    ($page->isActive(true) ? 'A' : ''));
//        }
//        echo PHP_EOL;
        
//        $prec = $this->getRenderInvisible(); // valeur initiale rétablie plus bas
//        $this->setRenderInvisible(true);
        
        // recherche de la page active de niveau 2
        $found = $this->findActive($container, 1, 1);
        if (!$found) {
            return '';
        }
        $activePageNiv2 = $found['page'];
        
        // recherche de la page active quelque soit son niveau pour déterminer la profondeur maxi utilisée plus bas
        $found = $this->findActive($container, 1);
        $maxDepth = $found['depth'] - 1;
        
//        $this->setRenderInvisible($prec);
        
        // on ne considère que le sous-menu correspondant à la page active de niveau 2
        $container = $activePageNiv2;
        
        // suppression des pages qu'on ne veut pas prendre en compte
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);
        $iterator->setMaxDepth(0);
        foreach ($iterator as $page) { /* @var $page \Zend\Navigation\Page\Mvc */
            $isActive = $page->isActive(true);
            if (!$isActive && $page->hasChildren()) {
                $page->removePages();
            }
        }
        
//        echo PHP_EOL;
//        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);
//        foreach ($iterator as $page) { /* @var $page \Zend\Navigation\Page\Mvc */
//            echo str_repeat('  ', $iterator->getDepth()) . sprintf("%s <%s> %s" . PHP_EOL, 
//                    $page->get('route'),
//                    $page->get('label'),
//                    ($page->isActive(true) ? 'A' : ''));
//        }
//        echo PHP_EOL;
        
        $html = $this->renderNormalMenu(
                $container,
                $options['ulClass'],
                $options['indent'],
                0,
                $maxDepth,
                $options['onlyActiveBranch'],
                $options['escapeLabels'],
                $options['addClassToListItem'],
                isset($options['liActiveClass']) ? $options['liActiveClass'] : null
        );

        return $html;     
    }
}