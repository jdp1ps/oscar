<?php
namespace UnicaenLdap\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Classe encapsulant les options de fonctionnement du module.
 * 
 * @author Laurent LECLUSE <laurent.lecluse at unicaen.fr>
 */
class ModuleOptions extends AbstractOptions
{
    /**
     * Nom DNS ou IP du serveur LDAP
     *
     * @var string
     */
    protected $host;

    /**
     * Numéro de port
     *
     * @var integer
     */
    protected $port;

    /**
     * Version d'Ldap à utiliser
     *
     * @var integer
     */
    protected $version;

    /**
     * Nom de domaine racine
     *
     * @var string
     */
    protected $baseDn;

    /**
     * 
     * 
     * @var boolean
     */
    protected $bindRequiresDn;

    /**
     * Login admin. au serveur
     *
     * @var string
     */
    protected $username;

    /**
     * Mot de passe d'accès au serveur
     *
     * @var string
     */
    protected $password;

    /**
     * 
     * @todo compléter la déf.
     * @var string
     */
    protected $accountFilterFormat;
    



    public function getIterator() {
        return new ArrayIterator($this);
    }

    /**
     * Retourne le nom DNS du serveur Ldap
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Modifie le nom DNS du serveur Ldap
     *
     * @param string $host
     * @return self
     */
    public function setHost($host)
    {
        $this->host = (string)$host;
        return $this;
    }

    /**
     * Retourne le numéro de port du serveur Ldap
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Modifie le numéro de port du serveur Ldap
     *
     * @param integer $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = (integer)$port;
        return $this;
    }

    /**
     * Retourne la version d'Ldap à utiliser
     *
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Modifie la version d'Ldap à utiliser
     *
     * @param integer $version
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = (integer)$version;
        return $this;
    }

    /**
     * Retourne le domaine racine à utiliser
     *
     * @return string
     */
    public function getBaseDn()
    {
        return $this->baseDn;
    }

    /**
     * Modifie le domaine racine à utiliser
     *
     * @param string $baseDn
     * @return self
     */
    public function setBaseDn($baseDn)
    {
        $this->baseDn = (string)$baseDn;
        return $this;
    }

    /**
     *
     * @todo compléter la définition
     * @return boolean
     */
    public function getBindRequiresDn()
    {
        return $this->bindRequiresDn;
    }

    /**
     *
     * @todo compléter la définition
     * @param boolean $bindRequiresDn
     * @return self
     */
    public function setBindRequiresDn($bindRequiresDn)
    {
        $this->bindRequiresDn = (boolean)$bindRequiresDn;
        return $this;
    }

    /**
     * Retourne le login de l'administrateur
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Modifie le login de l'administrateur
     *
     * @param string $username
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = (string)$username;
        return $this;
    }

    /**
     * Retourne le mot de passe de l'administrateur
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Modifie le mot de passe de l'administrateur
     *
     * @param string $password
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = (string)$password;
        return $this;
    }

    /**
     *
     * @todo compléter la définition
     * @return string
     */
    public function getAccountFilterFormat()
    {
        return $this->accountFilterFormat;
    }

    /**
     *
     * @todo compléter la définition
     * @param string $accountFilterFormat
     * @return self
     */
    public function setAccountFilterFormat($accountFilterFormat)
    {
        $this->accountFilterFormat = (boolean)$accountFilterFormat;
        return $this;
    }

    /**
     * Retourne un tableau d'options à transmettre à Zend\Ldap\Ldap
     * 
     * @return array
     */
    public function getLdap()
    {
        return array(
            'host'              => $this->getHost(),
	    'port'		=> $this->getPort(),
            'username'          => $this->getUsername(),
            'password'          => $this->getPassword(),
            'bindRequiresDn'    => $this->getBindRequiresDn(),
            'baseDn'            => $this->getBaseDn(),
        );
    }
}