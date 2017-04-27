<?php

namespace UnicaenAuth\Controller;

use Zend\Mvc\Controller\AbstractActionController;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UtilisateurController extends AbstractActionController
{
    /**
     * Traite les requêtes AJAX POST de sélection d'un profil utilisateur.
     * La sélection est mémorisé en session par le service AuthUserContext.
     */
    public function selectionnerProfilAction($addFlashMessage = true)
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->url()->fromRoute('home');
        }
        
        $role = $this->getRequest()->getPost('role');
        
        if ($role) {
            $this->getAuthUserContextService()->setSelectedIdentityRole($role);
        }
        
        if ($addFlashMessage) {
            $this->flashMessenger()->addSuccessMessage(
                    sprintf("Vous endossez à présent le profil utilisateur <strong>%s</strong>.",
                            $this->getAuthUserContextService()->getSelectedIdentityRole()->getRoleId()));
        }
        
        $viewModel = new \Zend\View\Model\ViewModel();
        $viewModel->setTerminal(true);
        
        return $viewModel;
    }
    
    /**
     * @return \UnicaenAuth\Service\UserContext
     */
    protected function getAuthUserContextService()
    {
        return $this->getServiceLocator()->get('AuthUserContext');
    }
}