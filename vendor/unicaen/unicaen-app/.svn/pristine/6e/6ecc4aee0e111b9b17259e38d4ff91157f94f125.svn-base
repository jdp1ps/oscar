<?php

namespace UnicaenApp\Options;

use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\ArrayUtils;

class ModuleOptions extends AbstractOptions
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    /**
     * @var array
     */
    protected $appInfos = array(
        'nom'     => "Application",
        'desc'    => "Description de l'application",
        'version' => "0.0.1",
        'date'    => "04/10/2012",
        'contact' => array('mail' => "crisi.applications@unicaen.fr", /*'tel'  => "02.31.56.62.08"*/),
        'mentionsLegales'        => "http://www.unicaen.fr/outils-portail-institutionnel/mentions-legales/",
        'informatiqueEtLibertes' => "http://www.unicaen.fr/outils-portail-institutionnel/informatique-et-libertes/",
    );

    /**
     * @var array
     */
    protected $db = array(
//         'connection' => array(
//             'orm_default' => array(
//                 'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
//                 'params' => array(
//                     'host'     => 'localhost',
//                     'port'     => '3306',
//                     'user'     => 'root',
//                     'password' => 'root',
//                     'dbname'   => 'squelette',
//                 )
//             ),
//         )
    );

    /**
     * @var array
     */
    protected $ldap = array(
//        'connection' => array(
//            'default' => array(
//                'params' => array(
//                    'host'                => 'ldap.unicaen.fr',
//                    'username'            => "uid=xxxxxxxx,ou=system,dc=unicaen,dc=fr",
//                    'password'            => "xxxxxxxxxx",
//                    'baseDn'              => "ou=people,dc=unicaen,dc=fr",
//                    'bindRequiresDn'      => true,
//                    'accountFilterFormat' => "(&(objectClass=posixAccount)(supannAliasLogin=%s))",
//                )
//            )
//        )
    );
    
    /**
     * @var array
     */
    protected $mail = array();
    
    /**
     * @var integer
     */
    protected $sessionRefreshPeriod = 0;
    
    /**
     * set app infos
     *
     * @param array $appInfos
     * @return ModuleOptions
     */
    public function setAppInfos(array $appInfos)
    {
        $this->appInfos = $appInfos;
        return $this;
    }

    /**
     * get app infos
     *
     * @return array
     */
    public function getAppInfos()
    {
        return $this->appInfos;
    }

    /**
     * set Ldap Connection Infos
     *
     * @param array $ldap
     * @return ModuleOptions
     */
    public function setLdap(array $ldap = array())
    {
        $this->ldap = ArrayUtils::merge($this->ldap, $ldap);
        return $this;
    }

    /**
     * get Ldap Connection Infos
     *
     * @return array
     */
    public function getLdap()
    {
        return (array)$this->ldap;
    }

    /**
     * Getter for db
     *
     * @return array
     */
    public function getDb()
    {
        return (array)$this->db;
    }
    
    /**
     * Setter for db
     *
     * @param array $db Value to set
     * @return self
     */
    public function setDb(array $db = array())
    {
        $this->db = ArrayUtils::merge($this->db, $db);
        return $this;
    }
    
    /**
     * Retourne les options concernant l'envoi de mail.
     * 
     * @return array
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Spécifie les options concernant l'envoi de mail.
     * 
     * @param array $mail
     * @return \UnicaenApp\Options\ModuleOptions
     */
    public function setMail(array $mail)
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * Retourne la période d'exécution de la requête de rafraîchissement de la session utilisateur.
     * La valeur 0 signifie qu'aucune requête n'est exécutée.
     * 
     * @return integer
     */
    function getSessionRefreshPeriod()
    {
        return $this->sessionRefreshPeriod;
    }
    
    /**
     * Spécifie la période d'exécution de la requête de rafraîchissement de la session utilisateur.
     * La valeur 0 désactive le mécanisme.
     * 
     * @param integer $sessionRefreshPeriod
     * @return \UnicaenApp\Options\ModuleOptions
     */
    function setSessionRefreshPeriod($sessionRefreshPeriod)
    {
        $this->sessionRefreshPeriod = (int) $sessionRefreshPeriod;
        return $this;
    }
}