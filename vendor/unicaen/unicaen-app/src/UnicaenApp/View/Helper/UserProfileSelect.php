<?php
namespace UnicaenApp\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Aide de vue permettant à l'utilisateur de sélectionner son profil courant parmi
 * les différents profils qu'il possède.
 * 
 * En l'occurence, cette aide de vue ne génère rien car une application n'utilisant 
 * que le module UnicaenApp ne fournit rien permettant à l'utilisateur de se connecter.
 * 
 * Cette aide de vue existe simplement dans le but d'être surchargée par le module UnicaenAuth.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class UserProfileSelect extends AbstractHelper
{
    /**
     * Point d'entrée.
     * 
     * @return AppConnection
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
        return '';
    }
}