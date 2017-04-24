<?php
namespace UnicaenApp\Form\View\Helper;

use UnicaenApp\Form\Element\MultipageFormNav as MultipageFormNavElement;
use UnicaenApp\Form\MultipageForm;
use UnicaenApp\Form\MultipageFormFieldsetInterface;
use Zend\Form\Form;
use Zend\View\Exception\InvalidArgumentException;
use Zend\Form\View\Helper\AbstractHelper;

/**
 * Aide de vue générant un form de formulaire multi-pages.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MultipageFormRecap extends AbstractHelper
{
    /**
     * Point d'entrée.
     * 
     * @return string
     */
    public function __invoke()
    {
        if (!($form = $this->getView()->form)) {
            throw new InvalidArgumentException("Aucun formulaire trouvé dans la vue.");
        }
        if (!$form instanceof Form) {
            throw new InvalidArgumentException("Le formulaire spécifié dans la vue est invalide.");
        }
        if (!count($form->getFieldsets())) {
            throw new InvalidArgumentException("Le formulaire spécifié dans la vue ne possède aucun fieldset.");
        }
        return $this->render($form);
    }
    
    /**
     * Génère le code HTML.
     * 
     * @param Form $form Formulaire concerné contenant les fieldsets
     * @return string
     */
    public function render(Form $form)
    {
        $templateForm = <<<EOS
<form method="POST">
%s
</form>
EOS;
        $templateFieldset = <<<EOS
    <fieldset>
        <legend>%s</legend>
        %s
    </fieldset>
EOS;
        $templateDtDd = <<<EOS
            <dt>%s</dt> <dd>%s</dd>
EOS;
        $data = $form->getValue();
        
        $markup = '';
        foreach($form->getFieldsets() as $fs) { /* @var \Zend\Form\Fieldset $fs */
            if ($fs instanceof MultipageFormFieldsetInterface) {
                $values = $fs->getLabelsAndValues($data);
            }
            else {
                $values = MultipageForm::getLabelsAndValues($fs, $data);
            }
            if ($values) {
                $dtdds = '';
                foreach ($values as $name => $array) {
                    $label = $array['label'];
                    $value = $array['value'];
                    if (is_array($value)) {
                        $value = $this->getView()->htmlList($value);
                    }
                    $dtdds .= sprintf($templateDtDd . PHP_EOL, $this->translate($label), $value);
                }
                $dl = '<dl>' . PHP_EOL . $dtdds . '        </dl>';
            }
            else {
                $dl = $this->translate("Néant");
            }
            $markup .= sprintf($templateFieldset, $this->translate($fs->getLabel()), $dl) . PHP_EOL;
        }
        
        $nav = new MultipageFormNavElement();
        $nav->setActivatePrevious(true)
            ->setActivateNext(false)
            ->setActivateSubmit(false)
            ->setActivateCancel(true)
            ->setActivateConfirm(true);
        $markup .= '    ' . $this->getView()->multipageFormRow($nav);
        
        $html = sprintf($templateForm, $markup) . PHP_EOL;
        
        return $html;
    }
    
    /**
     * 
     * @param string $message
     * @return string
     */
    protected function translate($message)
    {
        if (!($translator = $this->getTranslator())) {
            return $message;
        }
        return $translator->translate($message, $this->getTranslatorTextDomain());
    }
}