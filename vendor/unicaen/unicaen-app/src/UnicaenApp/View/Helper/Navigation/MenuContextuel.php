<?php

namespace UnicaenApp\View\Helper\Navigation;

use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Page\AbstractPage;

/**
 * Rendu d'une sous-partie du menu de navigation courant (qui tient compte des ACL),
 * avec différents filtrages et manipulations d'attributs possibles.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MenuContextuel extends AbstractMenu
{
    /**
     * @var \Zend\Navigation\Page\Mvc
     */
    protected $activePage;
    
    /**
     * Nom d'événement jQuery ajouté systématiquement comme classe CSS à chaque lien.
     * Événement déclenché dans le cadre de l'ouverture d'un lien dans une fenêtre modale,
     * lorsque le formulaire est POSTé.
     * @var string
     */
    protected $eventName;
    
    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param AbstractContainer $container [optional] container to operate on
     * @return self
     */
    public function __invoke($container = null)
    {
        $this->setRenderInvisible(true);

        $activePage = $this->getActivePage();
        if (!$activePage) {
            throw new \UnicaenApp\Exception\RuntimeException("Impossible d'afficher les liens de navigation: page de navigation active introuvable.");
        }
        $container = new \Zend\Navigation\Navigation($activePage->getPages());
        
        return parent::__invoke($container);
    }
    
    /**
     * {@inheritedDoc}
     */
    protected function htmlAttribs($attribs)
    {
        $attribs['data-event'] = $this->eventName;
        
        if ($this->targetid) {
            $attribs['data-id'] = $this->targetid;
        }
        
        return parent::htmlAttribs($attribs);
    }
    
    /**
     * {@inheritedDoc}
     */
    public function htmlify(AbstractPage $page, $escapeLabel = true, $addClassToListItem = false)
    {
        $this->eventName = $this->createEventName($page); // nom de l'événement jQuery qui sera déclenché
        
        $html = parent::htmlify($page, $escapeLabel, $addClassToListItem);
        
        return $html;
    }
    
    /**
     * Génère le nom de l'événement jQuery qui à déclencher lorsque le formulaire de
     * la fenêtre modale est POSTé.
     * 
     * @param AbstractPage $page
     */
    protected function createEventName(AbstractPage $page)
    {
        $params = $page->get('params');
        
        $eventName = array('event');
        if (method_exists($page, 'getRoute')) {
            $eventName[] = str_replace('/', '-', $page->getRoute());
        }
        if (isset($params['action'])) {
            $eventName[] = $params['action'];
        }
        $eventName = implode('-', $eventName);
        
        return $eventName;
    }
    
    /**
     * Retourne la page active.
     * 
     * @return \Zend\Navigation\Page\Mvc
     */
    protected function getActivePage()
    {
        if (null === $this->activePage) {
            // recherche de la page active (le filtrage est momentanément désactivé à cette occasion)
            $this->bypass = true;
            $active       = $this->findActive($this->getContainer(), 1);
            $this->bypass = false;

            if ($active) {
                $this->activePage = $active['page'];
            }
        }
        
        return $this->activePage;
    }
}