<?php

namespace UnicaenApp\View\Helper;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Form\Confirmer;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper facilitant (un peu) la demande de confirmation, côté vue.
 *
 * En couple avec le plugin de contrôleur ConfirmPlugin.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see \UnicaenApp\Controller\Plugin\ConfirmPlugin
 */
class ConfirmHelper extends AbstractHelper
{
    /**
     * Méthode magique.
     * 
     * @param string $message Message de confirmation à afficher
     * @return string Code HTML de la demande de confirmation
     */
    public function __invoke($message = null)
    {
        $form = $this->getView()->formConfirm;
        
        if (!$form instanceof Confirmer) {
            throw new LogicException("Le formulaire de confirmation trouvé dans la vue n'est pas du type attendu.");
        }
        
        $html = '';
        
        if ($form->getMessages()) {
            $html .= $this->getView()->formErrors($form);
        }
        
        $html .= sprintf('<p class="lead text-danger">%s</p>', $message ?: "Confirmez-vous cette action, svp ?");
        $html .= $this->getView()->form()->openTag($form);
        $html .= $this->getView()->formHidden($form->get('id'));
        $html .= $this->getView()->formHidden($form->get('security'));
        $html .= $this->getView()->formSubmit($form->get('confirm')->setAttribute('class', 'btn btn-primary'));
        $html .= $this->getView()->form()->closeTag();
        
        return $html;
    }
}