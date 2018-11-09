<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/11/18
 * Time: 10:05
 */

namespace Oscar\Service;


use Oscar\Exception\OscarException;

trait ConfigurationAwareTrait
{

    /**
     * @param $key
     * @return ConfigurationParser
     * @throws \Oscar\Exception\OscarException
     */
    public function getConfiguration($key){
        static $config;
        if( $config == null ){
            $config = new ConfigurationParser($this->getServiceLocator()->get('Config'));
        }
        return $config->getConfiguration($key);
    }

    /**
     * @return \Oscar\Entity\Authentification
     * @throws \Oscar\Exception\OscarException
     */
    public function getCurrentAuth(){
        $authentification = $this->getOscarUserContext()->getAuthentification();
        if( !$authentification ){
            throw new OscarException(_("'Vous n'êtes pas authentifié."));
        }
        return $authentification;
    }

    /**
     * @return \Oscar\Entity\Person
     * @throws \Oscar\Exception\OscarException
     */
    public function getCurrentPerson(){
        $person = $this->getOscarUserContext()->getCurrentPerson();
        if( !$person ){
            throw new OscarException(_("Votre compte n'est associé à aucune personne"));
        }
        return $person;
    }

    /**
     * @return OscarUserContext
     * @throws \Oscar\Exception\OscarException
     */
    protected function getOscarUserContext()
    {
        return $this->getServiceLocator()->get('OscarUserContext');
    }
}