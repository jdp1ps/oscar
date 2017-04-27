<?php

namespace UnicaenApp\Mvc\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use UnicaenApp\Filter\ModalInnerViewModelFilter;
use UnicaenApp\View\Model\ModalInnerViewModel;
use UnicaenApp\View\Model\ModalViewModel;

/**
 * Ecoute l'événement 'render' pour imbriquer le modèle de vue courant dans celui d'une fenêtre modale ssi :
 * - le paramètre GET 'modal' est présent dans la requête ; 
 * - le modèle de vue fourni par l'action courante n'est pas déjà celui d'une fenêtre modale.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see \UnicaenApp\View\Model\ModalViewModel
 * @see \UnicaenApp\View\Model\ModalInnerViewModel
 */
class ModalListener implements ListenerAggregateInterface
{
    use \Zend\EventManager\ListenerAggregateTrait;

    protected $paramName = 'modal';

    /**
     * 
     * @param MvcEvent $e
     */
    public function injectModalViewModel(MvcEvent $e)
    {
        $request = $e->getRequest();
        if (!$request instanceof Request || !$request->isXmlHttpRequest()) {
            return;
        }

        $modal = (bool) $request->getQuery($this->paramName, $request->getPost($this->paramName, 0));
        if (!$modal) {
            return;
        }
        
        $result = $e->getResult();
        
        if ($result instanceof ModalViewModel || $result instanceof ModalInnerViewModel) {
            return;
        }
        
        if (!$result instanceof ViewModel) {
            $result = new ViewModel($result);
        }

        $f = new ModalInnerViewModelFilter();
        $modalViewModel = $f->filter($result);

        $e->setResult($modalViewModel);
        $e->setViewModel($modalViewModel);
    }
    
    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'injectModalViewModel'));
    }
}