<?php
namespace UnicaenAuth\View\Helper;

/**
 * Aide de vue de génération du lien de connexion/déconnexion à l'appli selon qu'un 
 * utilisateur est connecté ou pas.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class UserConnection extends UserAbstract
{
    /**
     * Point d'entrée.
     *
     * @return UserConnection
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Retourne le code HTML généré par cette aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        $template = '%s';
        $out = sprintf($template, $this->createConnectionLink());

        return $out;
    }

    /**
     * 
     * @return string
     */
    protected function createConnectionLink()
    {
        $identity = $this->getIdentity();

        $urlHelper = $this->getView()->plugin('url');

        $template = '<a class="navbar-link user-connection" href="%s" title="%s">%s</a>';
        if (!$identity) {
            $href  = $urlHelper('zfcuser/login');
            $lib   = "Connexion";
            $title = "Affiche le formulaire d'authentification";
        }
        else {
            $href  = $urlHelper('zfcuser/logout');
            $lib   = "Déconnexion";
            $title = "Supprime les informations de connexion";
        }
        if ($this->getTranslator()) {
            $lib   = $this->getTranslator()->translate($lib, $this->getTranslatorTextDomain());
            $title = $this->getTranslator()->translate($title, $this->getTranslatorTextDomain());
        }
        $link = sprintf($template, $href, $title, $lib);

        return $link;
    }
}