<?php
namespace UnicaenAuth\View\Helper;

/**
 * Aide de vue affichant toutes les infos concernant l'utilisateur courant.
 * C'est à dire :
 *  - "Aucun" + lien de connexion OU BIEN nom de l'utilisateur connecté + lien de déconnexion
 *  - profil de l'utilisateur connecté
 *  - infos administratives sur l'utilisateur
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class UserCurrent extends UserAbstract
{
    /**
     * @var bool
     */
    protected $affectationFineSiDispo = false;

    /**
     * Point d'entrée.
     * 
     * @param boolean $affectationFineSiDispo Indique s'il faut prendre en compte l'affectation
     * plus fine (ucbnSousStructure) si elle existe, à la place de l'affectation standard (niveau 2)
     * @return self 
     */
    public function __invoke($affectationFineSiDispo = false)
    {
        $this->setAffectationFineSiDispo($affectationFineSiDispo);
        return $this;
    }
    
    /**
     * Retourne le code HTML généré par cette aide de vue.
     * 
     * @return string 
     */
    public function __toString()
    {
        $id                    = 'user-current-info';
        $userStatusHelper      = $this->getView()->plugin('userStatus'); /* @var $userStatusHelper \UnicaenAuth\View\Helper\UserStatus */
        $status                = $userStatusHelper(false);
        $userProfileSelectable = true;
        
        if ($this->getIdentity()) {
            if ($userProfileSelectable) {
		        // DS : cas où aucun rôle n'est sélectionné, on affiche le rôle "user"
		        $role = $this->getUserContext()->getSelectedIdentityRole() ?: $this->getUserContext()->getIdentityRole('user');
                $status .= sprintf(", <small>%s</small>", !method_exists($role, '__toString') ? $role->getRoleId() : $role);
            }
        
            $userProfileHelper = $this->getView()->plugin('userProfile'); /* @var $userProfileHelper \UnicaenAuth\View\Helper\UserProfile */
            $userProfileHelper->setUserProfileSelectable($userProfileSelectable);
            
            $userInfoHelper = $this->getView()->plugin('userInfo'); /* @var $userInfoHelper \UnicaenAuth\View\Helper\UserInfo */
            
            $content = $userProfileHelper . $userInfoHelper($this->getAffectationFineSiDispo());
        }
        else {
            $content = _("Aucun");
            if ($this->getTranslator()) {
                $content = $this->getTranslator()->translate($content, $this->getTranslatorTextDomain());
            }
        }

        $content = htmlspecialchars(preg_replace('/\r\n|\n|\r/', '', $content));

        $title = _("Utilisateur connecté à l'application");
        if ($this->getTranslator()) {
            $title = $this->getTranslator()->translate($title, $this->getTranslatorTextDomain());
        }

        $out = <<<EOS
<a class="navbar-link" 
   id="$id" 
   title="$title" 
   data-placement="bottom" 
   data-toggle="popover" 
   data-content="$content" 
   href="#">$status</a>
EOS;
        $out .= PHP_EOL;
        
        $js = <<<EOS
$(function() {
    $("#$id").popover({ html: true, container: '#navbar' });
});
EOS;
        $this->getView()->plugin('inlineScript')->offsetSetScript(1000, $js);
        
        return $out;
    }

    /**
     * Indique si l'affichage de l'affectation fine éventuelle est activé ou non.
     * 
     * @return bool
     */
    public function getAffectationFineSiDispo()
    {
        return $this->affectationFineSiDispo;
    }

    /**
     * Active ou non l'affichage de l'affectation fine éventuelle.
     * 
     * @param bool $affectationFineSiDispo
     * @return self
     */
    public function setAffectationFineSiDispo($affectationFineSiDispo = true)
    {
        $this->affectationFineSiDispo = $affectationFineSiDispo;
        return $this;
    }
}
