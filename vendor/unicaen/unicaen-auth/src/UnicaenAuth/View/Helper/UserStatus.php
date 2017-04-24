<?php
namespace UnicaenAuth\View\Helper;

/**
 * Aide de vue générant les éléments concernant le statut de connexion à l'appli
 * de l'utilisateur.
 *
 * À savoir :
 * - Si un utilisateur est connecté : l'identité de l'utilisateur connecté et
 *   éventuellement le lien pointant vers l'URL de déconnexion.
 * - Si aucun utilisateur n'est connecté : le libellé "Aucun" et éventuellement
 *   le lien pointant vers l'URL de connexion.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class UserStatus extends UserAbstract
{
    /**
     * @var bool
     */
    protected $displayConnectionLink = true;

    /**
     * Retourne l'instance de ce view helper.
     *
     * @param boolean $displayConnectionLink Inclure ou pas le lien de connexion/déconnexion ?
     * @return self
     */
    public function __invoke($displayConnectionLink = true)
    {
        $this->setDisplayConnectionLink($displayConnectionLink);
        return $this;
    }

    /**
     * Retourne le code HTML généré par cette aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        $parts = [];

        $parts[] = $this->createStatusContainer();

        if ($this->getDisplayConnectionLink()) {
            $userConnectionHelper = $this->getView()->plugin('userConnection'); /* @var $userConnectionHelper UserConnection */
            $parts[] = (string) $userConnectionHelper;
        }

        $out = implode(' | ', $parts);

        return $out;
    }

    /**
     *
     * @return string
     */
    protected function createStatusContainer()
    {
        if (($identity = $this->getIdentity())) {
            if (method_exists($identity, '__toString')) {
                $name = (string) $identity;
            }
            elseif (method_exists($identity, 'getDisplayName')) {
                $name = $identity->getDisplayName();
            }
            elseif (method_exists($identity, 'getUsername')) {
                $name = $identity->getUsername();
            }
            elseif (method_exists($identity, 'getId')) {
                $name = $identity->getId();
            }
            else {
                $name = sprintf('<span title="Erreur: identité inattendue (%s)">???</span>',
                        is_object($identity) ? get_class($identity) : gettype($identity));
            }
        }
        else {
            $name = _("Vous n'êtes pas connecté(e)");
            if ($this->getTranslator()) {
                $name = $this->getTranslator()->translate($name, $this->getTranslatorTextDomain());
            }
        }

        $out = sprintf('<span class="glyphicon glyphicon-user"></span> <strong>%s</strong>', $name);

        return $out;
    }

    /**
     * Indique si le lien de connexion/déconnexion est affiché ou non
     *
     * @return boolean
     */
    public function getDisplayConnectionLink()
    {
        return $this->displayConnectionLink;
    }

    /**
     * Affiche ou non le lien de connexion/déconnexion
     *
     * @param boolean $displayConnectionLink
     * @return self
     */
    public function setDisplayConnectionLink($displayConnectionLink = true)
    {
        $this->displayConnectionLink = $displayConnectionLink;
        return $this;
    }
}