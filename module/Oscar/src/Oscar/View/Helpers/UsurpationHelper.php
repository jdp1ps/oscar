<?php

namespace Oscar\View\Helpers;

use UnicaenApp\Form\View\Helper\FormControlGroup;
use UnicaenAuthentification\Options\ModuleOptions;
use UnicaenUtilisateur\View\Helper\UserAbstract;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Text;
use Laminas\Form\Form;
use Laminas\Form\View\Helper\Form as FormHelper;
use Laminas\Form\View\Helper\FormElement;
use Laminas\View\Renderer\PhpRenderer;

class UsurpationHelper extends UserAbstract
{
    /**
     * @var PhpRenderer
     */
    protected $view;

    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $usurpationEnabled = false;

    /**
     * @var bool
     */
    private $usurpationEnCours = false;

    /**
     * @var bool
     */
    private $asButton = false;

    /**
     * Point d'entrée.
     *
     * @return self
     */
    public function __invoke(): self
    {
        return $this;
    }
    
    /**
     * Retourne le code HTML généré par cette aide de vue.
     * Equivalent à {@see renderAsTextfield()}.
     * 
     * @return string 
     */
    public function __toString(): string
    {
        return $this->renderAsTextfield();
    }

    /**
     * @param AbstractUser $user
     * @param string $buttonLabel
     * @return string
     */
    public function renderAsButton(AbstractUser $user, string $buttonLabel = 'Usurper'): string
    {
        if ($this->usurpationEnCours) {
            return $this->renderStopButton();
        }
        if (!$this->usurpationEnabled) {
            return '';
        }

        $this->asButton = true;

        $form = $this->createForm();

        $identity = $form->get('identity');
        $submit = $form->get('submit');

        $identity->setValue($user->getUsername());
        $submit
            ->setValue($buttonLabel)
            ->setAttribute('title', "Usurper l'identité de " . $user);

        /** @var FormHelper $formHelper */
        $formHelper = $this->view->plugin('form');
        /** @var FormElement $formElementHelper */
        $formElementHelper = $this->view->plugin('formElement');

        $html = '';
        $html .= $formHelper->openTag($form);
        $html .= $formElementHelper->__invoke($identity);
        $html .= $formElementHelper->__invoke($submit);
        $html .= $formHelper->closeTag();

        return $html;
    }

    /**
     * @return string
     */
    public function renderAsTextfield(): string
    {
        if ($this->usurpationEnCours) {
            return $this->renderStopButton();
        }
        if (!$this->usurpationEnabled) {
            return '';
        }
        $this->asButton = false;

        $form = $this->createForm();

        $identity = $form->get('identity');
        $submit = $form->get('submit');

        /** @var FormHelper $formHelper */
        $formHelper = $this->view->plugin('form');
        /** @var FormControlGroup $formControlGroupHelper */
        $formControlGroupHelper = $this->view->plugin('formControlGroup');

        $html = '';
        $html .= $formHelper->openTag($form);
        $html .= "<div><strong>Usurpation d'identité :</strong></div>";
        $html .= $formControlGroupHelper->__invoke($identity);
        $html .= $formControlGroupHelper->__invoke($submit);
        $html .= $formHelper->closeTag();

        return $html;
    }

    /**
     * @return string
     */
    protected function renderStopButton(): string
    {
        $url = $this->view->url('utilisateur/default', ['action' => 'stopper-usurpation']);

        return <<<EOS
<p class="user-usurpation-stop-btn">
    <a class="btn btn-danger" href="$url">Stopper l'usurpation</a>
</p>
EOS;
    }

    protected function createForm(): Form
    {
        $form = new Form('user-usurpation-form');
        $form->setAttributes([
            'id' => $formId = uniqid('user-usurpation-form'),
            'class' => 'user-usurpation-form',
            'action' => $this->url,
        ]);

        if ($this->asButton) {
            $identity = null;
            $identity = new Hidden('identity');
            $identity->setAttributes([
                'id' => 'user-usurpation-hidden',
            ]);
        } else {
            $identity = new Text('identity');
            $identity->setAttributes([
                'class' => 'user-usurpation-input',
                'placeholder' => "Identifiant utilisateur",
            ]);
        }

        $submit = new Submit('submit');
        $submit->setValue("Usurper");
        $submit->setAttributes([
            'class' => 'user-usurpation-submit btn btn-danger',
        ]);

        $form->add($identity);
        $form->add($submit);

        return $form;
    }

    /**
     * @param string $url
     * @return self
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @param ModuleOptions $moduleOptions
     * @return self
     */
    public function setModuleOptions(ModuleOptions $moduleOptions): self
    {
        $this->moduleOptions = $moduleOptions;

        return $this;
    }

    /**
     * @param bool $usurpationEnabled
     * @return self
     */
    public function setUsurpationEnabled($usurpationEnabled = true): self
    {
        $this->usurpationEnabled = $usurpationEnabled;

        return $this;
    }

    public function isEnabled() :bool
    {
        return $this->usurpationEnabled || $this->usurpationEnCours;
    }

    /**
     * @param bool $usurpationEnCours
     * @return self
     */
    public function setUsurpationEnCours(bool $usurpationEnCours): self
    {
        $this->usurpationEnCours = $usurpationEnCours;

        return $this;
    }
}