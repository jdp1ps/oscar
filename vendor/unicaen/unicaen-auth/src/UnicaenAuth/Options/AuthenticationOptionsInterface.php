<?php

namespace UnicaenAuth\Options;

interface AuthenticationOptionsInterface
{

    /**
     * Spécifie si l'utilisateur authentifié doit être enregistré dans la base
     * de données de l'appli
     *
     * @param bool $flag
     * @return ModuleOptions
     */
    public function setSaveLdapUserInDatabase($flag = true);

    /**
     * Retourne la valeur du flag spécifiant si l'utilisateur authentifié doit être 
     * enregistré dans la base de données de l'appli
     *
     * @return bool
     */
    public function getSaveLdapUserInDatabase();

    /**
     * set cas auth activation flag
     *
     * @param bool $activated
     * @return ModuleOptions
     */
    public function setCasAuthenticationActivated($activated = true);

    /**
     * set cas auth activation flag
     *
     * @return bool
     */
    public function getCasAuthenticationActivated();

    /**
     * set cas connection informations
     *
     * @param array $casConnectionInfos
     * @return ModuleOptions
     */
    public function setCasConnectionInfos($casConnectionInfos);

    /**
     * get cas connection informations
     *
     * @return array
     */
    public function getCasConnectionInfos();
}
