<?php

namespace UnicaenApp\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;
use Zend\View\Resolver\TemplatePathStack;

/**
 * Cette aide de vue intercepte le clic sur un lien ayant la classe CSS "ajax-modal", 
 * lance la requête correspondante en AJAX et ouvre le résultat dans une fenêtre modale Bootstrap.
 * 
 * Elle simplifie également la gestion d'un éventuel formulaire présent dans cette fenêtre modale.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModalAjaxDialog extends AbstractHelper
{
    protected $dialogDivId;
    
    /**
     * Point d'entrée.
     * 
     * @param string $dialogDivId DOM id du conteneur de la fenêtre modale
     * @return string
     */
    public function __invoke($dialogDivId = null)
    {
        $this->dialogDivId = $dialogDivId;
        
        return $this->render();
    }
    
    /**
     * 
     * @return string Code HTML
     */
    public function render()
    {
        $stack = new TemplatePathStack(array('script_paths' => array(__DIR__ . '/view')));
        $model = new ViewModel();
        $model->setTemplate('modal.phtml')
              ->setVariables(array(
                  'dialogDivId' => $this->getDialogId(),
              ));
        $this->getView()->resolver()->attach($stack);
        
        return $this->getView()->render($model);
    }
    
    /**
     * 
     * @return string
     */
    protected function getDialogId()
    {
        if (null === $this->dialogDivId) {
            $this->dialogDivId = uniqid('div-dialog-');
        }
        
        return $this->dialogDivId;
    }
}