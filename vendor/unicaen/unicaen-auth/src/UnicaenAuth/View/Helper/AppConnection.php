<?php
namespace UnicaenAuth\View\Helper;

/**
 * Aide de vue générant le lien et les infos concernant la connexion à l'application.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class AppConnection extends \UnicaenApp\View\Helper\AppConnection
{

    /**
     * Retourne le code HTML généré par cette aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        $connexion = [];

        if (($tmp = $this->getView()->plugin('userCurrent'))) {
            $connexion[] = "" . $tmp;
        }
        if (($tmp = $this->getView()->plugin('userConnection'))) {
            $connexion[] = "" . $tmp;
        }

        return implode(' | ', $connexion);
    }
}