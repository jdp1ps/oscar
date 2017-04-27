<?php
namespace UnicaenAuth\View\Helper;

use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Form\Element\Radio;

/**
 * Aide de vue dessinant un item de sélection d'un profil utilisateur.
 * Utilisé par l'aide de vue UserProfileSelect.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 * @see UserProfileSelect
 */
class UserProfileSelectRadioItem extends UserAbstract
{
    /**
     * @var RoleInterface
     */
    protected $role;

    /**
     * @var bool
     */
    protected $selected;

    /**
     * @var string
     */
    protected $formClass;

    /**
     *
     * @param RoleInterface $role
     * @param bool $selected
     * @return self
     */
    public function __invoke(RoleInterface $role, $selected = false)
    {
        $this->role     = $role;
        $this->selected = $selected;

        return $this;
    }

    /**
     * Retourne le code HTML généré par cette aide de vue.
     *
     * @return string
     */
    public function render()
    {
        $radio = $this->createRadio();

        $html = $this->getView()->formRadio($radio);

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
     * @return Radio
     */
    protected function createRadio()
    {
        $id = $this->role->getRoleId();
        $inputClass = 'user-profile-select-input';

	$roleToString = method_exists($this->role, '__toString') ? (string) $this->role : $this->role->getRoleId();

        // rendu sous forme de radio
        $radio = new Radio('role');
        $radio
                ->setValueOptions([$id => $roleToString])
                ->setAttribute('class', $inputClass)
                ->setAttribute('title', "Cliquez pour changer de profil courant")
                ->setValue($this->selected ? $id : null);

        return $radio;
    }
}