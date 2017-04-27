<?php

namespace UnicaenApp\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;
use UnicaenApp\Filter\ModalInnerViewModelFilter;
use UnicaenApp\Exception\LogicException;

/**
 * Plugin de contrôleur permettant d'imbriquer un modèle de vue dans un modèle
 * de vue correspondant à la DIV interne d'une fenêtre modale Bootstrap 3 (div.modal-dialog).
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModalInnerViewModel extends AbstractPlugin
{
    protected $request;
      
    /**
     * Point d'entrée.
     * 
     * @param null|array|Traversable|ViewModel $viewModel
     * @return ModalInnerViewModel
     * @throws LogicException
     */
    public function __invoke($viewModel)
    {
        if (!$viewModel instanceof ViewModel) {
            $viewModel = new ViewModel($viewModel);
        }
        
        $f = new ModalInnerViewModelFilter();
        
        $modalViewModel = $f->filter($viewModel);
        $modalViewModel->setTerminal($this->getRequest()->isXmlHttpRequest()); // Turn off the layout for AJAX requests
        
        return $modalViewModel;
    }
    
    /**
     * 
     * @param \Zend\Http\Request $request
     * @return \UnicaenApp\Controller\Plugin\ModalInnerViewModel
     */
    public function setRequest(\Zend\Http\Request $request)
    {
        $this->request = $request;
        
        return $this;
    }
    
    /**
     * 
     * @return \Zend\Http\Request
     */
    public function getRequest()
    {
        if (null === $this->request) {
            if ($this->getController()) {
                $this->request = $this->getController()->getRequest();
            }
        }
        return $this->request;
    }
}