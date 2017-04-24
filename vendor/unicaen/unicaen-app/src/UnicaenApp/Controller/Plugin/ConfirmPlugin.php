<?php

namespace UnicaenApp\Controller\Plugin;

use UnicaenApp\Form\Confirmer;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;

/**
 * Plugin facilitant (un peu) la demande de confirmation, côté contrôleur.
 * 
 * En couple avec l'aide de vue ConfirmHelper.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see \UnicaenApp\View\Helper\ConfirmHelper
 */
class ConfirmPlugin extends AbstractPlugin
{
    protected $form;
    protected $viewModel;
    
    /**
     * Méthode magique.
     * 
     * @return self
     */
    public function __invoke()
    {
        return $this;
    }
    
    /**
     * Traite la requête.
     * 
     * Si la requête est de type POST et que le formulaire de confirmation est valide,
     * cette méthode retourne le tableau des données POSTées.
     * Sinon elle retourne un ViewModel.
     * 
     * @return array|ViewModel
     */
    public function execute()
    {
        $form      = $this->getForm();
        $viewModel = $this->getViewModel();
        
        if ($this->getController()->getRequest()->isPost()) {
            $data = $this->getController()->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                return $data->toArray();
            }
        }
        
        return $viewModel;
    }
    
    /**
     * Positionne des messages d'erreurs sur le formulaire de confirmation.
     * 
     * @param array $messages
     * @return self
     */
    public function setMessages(array $messages = [])
    {
        $this->getForm()->setMessages(['confirm' => $messages]);
        
        return $this;
    }
    
    /**
     * Retourne le ViewModel utilisé.
     * 
     * @return ViewModel
     */
    public function getViewModel()
    {
        if (null === $this->viewModel) {
            $this->viewModel = new ViewModel();
            $this->viewModel->setVariables(['formConfirm' => $this->getForm()]);
        }
        
        return $this->viewModel;
    }
    
    /**
     * Retourne le formulaire de confirmation.
     * 
     * @return Confirmer
     */
    public function getForm()
    {
        if (null === $this->form) {
            $this->form = new Confirmer();
            $this->form->setAttribute('action', $this->getController()->url()->fromRoute(null, array(), array(), true));
        }
        
        return $this->form;
    }
}