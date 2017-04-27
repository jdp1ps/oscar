<?php
namespace UnicaenAuth\View\Helper;

use Zend\Permissions\Acl\Role\RoleInterface;

/**
 * Aide de vue permettant à l'utilisateur de sélectionner son profil courant parmi
 * les différents profils qu'il possède.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class UserProfileSelect extends UserAbstract
{
    /**
     * @var \UnicaenAuth\Service\UserContext
     */
    protected $userContextService;

    /**
     * @var string
     */
    protected $formClass;

    /**
     * @var bool
     */
    protected $asSelect = false;

    /**
     * @var string
     */
    protected $redirectRoute;

    /**
     * Retourne le code HTML généré par cette aide de vue.
     *
     * @return string
     */
    public function render()
    {
        $formClass  = 'user-profile-select-form';
        $inputClass = 'user-profile-select-input';

        $form = new \Zend\Form\Form();
        $form->setAttribute('class', "$formClass " . $this->formClass);

        $html = $this->getView()->form()->openTag($form);

        // rendu sous forme d'un select
        if ($this->asSelect) {
            $rolesAsOptions = $this->getIdentityRolesAsOptions();

            if (!$rolesAsOptions) {
                return '';
            }

            $select = new \Zend\Form\Element\Select('role');
            $select
                    ->setValueOptions($rolesAsOptions)
                    ->setAttributes([
                        'class' => $inputClass,
                        'value' => $this->getSelectedIdentityRole(),
                    ]);

            $html .= $this->getView()->formControlGroup($select);
        }
        // rendu sous forme de radios
        else {
            $roles = $this->getSelectableRoles();

            foreach ($roles as $id => $role) {
                $selected = ($id === $this->getSelectedIdentityRole());
                $html .= '<div class="radio">' . $this->getView()->userProfileSelectRadioItem($role, $selected) . '</div>';
            }
        }

        $html .= $this->getView()->form()->closeTag();

        $url = $this->getView()->url('utilisateur/default', ['action' => 'selectionner-profil']);
        $redirectUrl = $this->getView()->url($this->redirectRoute ?: 'home');

        $html .= <<<EOS
<script>
    $(function() {
        $("input.$inputClass").change(function() { submitProfile(); }).tooltip({ delay: 500, placement: 'left' });
    });
    function submitProfile()
    {
        $("body *").css('cursor', 'wait');
        $.post("$url", $(".$formClass").serializeArray(), function() { $(location).attr('href', "$redirectUrl"); });
    }
</script>
EOS;

        return $html;
    }

    /**
     * Retourne le code HTML généré par cette aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Interroge le service pour abtenir le rôle sélectionné.
     *
     * @return mixed
     */
    protected function getSelectedIdentityRole()
    {
        $role = $this->getUserContext()->getSelectedIdentityRole();
        if ($role instanceof RoleInterface) {
            return $role->getRoleId();
        }
        return $role;
    }

    /**
     * Formatte et traduit les rôles.
     *
     * @return array
     */
    protected function getIdentityRolesAsOptions()
    {
        $roles = $this->getSelectableRoles();

        foreach ($roles as $id => $role) {
            $lib = '?';
            if (is_object($role) && method_exists($role, '__toString')) {
                $lib = (string) $role;
            }
            elseif ($role instanceof RoleInterface) {
                $lib = $role->getRoleId();
            }
            elseif (is_string($role)) {
                $lib = $role;
            }
            if ($this->getTranslator()) {
                $lib = $this->getTranslator()->translate($lib, $this->getTranslatorTextDomain());
            }
            $roles[$id] = $lib;
        }

        return $roles;
    }

    /**
     *
     * @return array id => role
     */
    protected function getSelectableRoles()
    {
        return $this->getUserContext()->getSelectableIdentityRoles();
    }

    public function setFormClass($formClass)
    {
        $this->formClass = $formClass;
        return $this;
    }

    public function setAsSelect($asSelect)
    {
        $this->asSelect = $asSelect;
        return $this;
    }

    public function setRedirectRoute($redirectRoute)
    {
        $this->redirectRoute = $redirectRoute;
        return $this;
    }
}