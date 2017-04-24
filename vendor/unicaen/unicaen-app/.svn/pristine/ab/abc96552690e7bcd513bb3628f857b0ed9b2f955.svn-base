<?php
namespace UnicaenApp\Form\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

/**
 * Génère le marquage HTML pour afficher à la fois un message d'erreur global de 
 * validation d'un formulaire ainsi que la liste des erreurs de tous les éléments de formulaires.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class FormErrors extends AbstractHelper
{
    protected $message = "Attention!";
    
    /**
     * 
     * @param \Zend\Form\Form $form
     * @return string|\UnicaenApp\Form\View\Helper\FormErrors
     */
    public function __invoke(\Zend\Form\Form $form = null, $message = null)
    {
        if (null === $form) {
            return $this;
        }
        
        if ($message) {
            $this->setMessage($message);
        }
        
        return $this->render($form);
    }
    
    /**
     * Rendu.
     * 
     * @param \Zend\Form\Form $form
     * @return string Code HTML
     */
    public function render(\Zend\Form\Form $form)
    {
        if (!$form->getMessages()) {
            return '';
        }
        
        $message = $this->getMessage();
                
        // extraction des messages d'erreur (ce sont les feuilles du tableau des erreurs au sein du formulaire)
        $messages = array();
        $callback = function($value) use (&$messages) { $messages[] = $value; };
        $tmp = $form->getMessages();
        array_walk_recursive($tmp, $callback);
        
        // traduction des messages
        if ($this->getTranslator()) {
            $message = $this->getTranslator()->translate($message, $this->getTranslatorTextDomain());
            foreach ($messages as $key => $msg) {
                $messages[$key] = $this->getTranslator()->translate($msg, $this->getTranslatorTextDomain());
            }
        }
        
        $list = $this->getView()->htmlList(array_unique($messages));
    
        $markup = <<<EOS
<div class="alert alert-danger">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <strong>$message</strong>
    $list
</div>
EOS;
        return $markup . PHP_EOL;
    }
    
    /**
     * Retourne le message d'en-tête.
     * 
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Spécifie le message d'en-tête.
     * 
     * @param string $message
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
}