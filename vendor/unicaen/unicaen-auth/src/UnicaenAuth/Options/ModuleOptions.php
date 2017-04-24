<?php
namespace UnicaenAuth\Options;

/**
 * Classe encapsulant les options de fonctionnement du module.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class ModuleOptions extends \ZfcUser\Options\ModuleOptions
{
    /**
     * @var array
     */
    protected $usurpationAllowedUsernames = [];

    /**
     * @var bool
     */
    protected $saveLdapUserInDatabase = false;

    /**
     * @var array
     */
    protected $cas = [];

    /**
     * @var string
     */
    protected $entityManagerName = 'doctrine.entitymanager.orm_default';



    /**
     * set usernames allowed to make usurpation
     *
     * @param array $usurpationAllowedUsernames
     *
     * @return ModuleOptions
     */
    public function setUsurpationAllowedUsernames(array $usurpationAllowedUsernames = [])
    {
        $this->usurpationAllowedUsernames = $usurpationAllowedUsernames;

        return $this;
    }



    /**
     * get usernames allowed to make usurpation
     *
     * @return array
     */
    public function getUsurpationAllowedUsernames()
    {
        return $this->usurpationAllowedUsernames;
    }



    /**
     * Spécifie si l'utilisateur authentifié doit être enregistré dans la base
     * de données de l'appli
     *
     * @param bool $flag
     *
     * @return ModuleOptions
     */
    public function setSaveLdapUserInDatabase($flag = true)
    {
        $this->saveLdapUserInDatabase = (bool)$flag;

        return $this;
    }



    /**
     * Retourne la valeur du flag spécifiant si l'utilisateur authentifié doit être
     * enregistré dans la base de données de l'appli
     *
     * @return bool
     */
    public function getSaveLdapUserInDatabase()
    {
        return $this->saveLdapUserInDatabase;
    }



    /**
     * set cas connection params
     *
     * @param array $cas
     *
     * @return ModuleOptions
     */
    public function setCas(array $cas = [])
    {
        $this->cas = $cas;

        return $this;
    }



    /**
     * get cas connection params
     *
     * @return array
     */
    public function getCas()
    {
        return $this->cas;
    }



    /**
     * @return string
     */
    public function getEntityManagerName()
    {
        return $this->entityManagerName;
    }



    /**
     * @param string $entityManagerName
     *
     * @return ModuleOptions
     */
    public function setEntityManagerName($entityManagerName)
    {
        $this->entityManagerName = $entityManagerName;

        return $this;
    }


}