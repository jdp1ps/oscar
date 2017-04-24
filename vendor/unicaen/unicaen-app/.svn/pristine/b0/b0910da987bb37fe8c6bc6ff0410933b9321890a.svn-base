<?php

namespace UnicaenApp\Controller\Plugin;

use UnicaenApp\Exception\LogicException;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;

/**
 * Plugin de contrôleur permettant d'imbriquer un modèle de vue dans un modèle
 * de vue correspondant à la DIV interne d'un popover Bootstrap 3 (div.popover).
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class PopoverInnerViewModel extends AbstractPlugin
{
    protected $request;

    /*******************************************************
     <div class="arrow"></div>
        <h3 class="popover-title">[paramètre $title]</h3>
        <div class="popover-content">
            [...]                      > marquage généré
        </div>
    ********************************************************/

    /**
     * Point d'entrée.
     *
     * @param \Zend\View\Model\ViewModel $viewModel
     * @param string $title
     * @param string $displaySubmit
     * @return \Zend\View\Model\ViewModel
     * @throws LogicException
     */
    public function __invoke(ViewModel $viewModel, $title = null, $displaySubmit = false)
    {
        $title = $title ?: $viewModel->getVariable('title');
        if (null === $title) {
            throw new LogicException("Aucun titre fourni.");
        }

        $viewModel->setTerminal(false);

        $popoverViewModel = new ViewModel();
        $popoverViewModel->setTemplate('unicaen-app/popover-wrapper.phtml')
                ->addChild($viewModel, 'content')
                ->setTerminal($this->getRequest()->isXmlHttpRequest()) // Turn off the layout for AJAX requests
                ->setVariables(array(
                    'title'         => $title,
                    'displaySubmit' => $displaySubmit,
                ));
        
        return $popoverViewModel;
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