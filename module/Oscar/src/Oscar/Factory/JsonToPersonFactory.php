<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-15 16:32
 * @copyright Certic (c) 2017
 */

namespace Oscar\Factory;


use Oscar\Entity\Person;
use Oscar\Exception\OscarException;

/**
 * Class JsonToPersonFactory
 * Cette classe permet de générer des objets Person à partir de données JSON.
 * @package Oscar\Factory
 */
class JsonToPersonFactory extends JsonToObject
{
    public function __construct()
    {
        parent::__construct(['uid', 'firstname', 'lastname']);
    }

    public function getInstance( $jsonData ){
        $person = new Person();
        return $this->hydrateWithDatas($person, $jsonData);
    }


    function hydrateWithDatas($object, $jsonData, $connectorName = null)
    {

        ////////////////////////////////////////////////////////////////////////
        ///
        /// Champs obligatoires
        if( !property_exists($jsonData, 'uid') )
            throw new OscarException("Le champ 'uid' est obligatoire.");

        if( !property_exists($jsonData, 'firstname') )
            throw new OscarException("Le champ 'firstname' est manquant pour l'entrée UID = " . $jsonData->uid);

        if( !property_exists($jsonData, 'lastname') )
            throw new OscarException("Le champ 'lastname' est manquant pour l'entrée UID = " . $jsonData->uid);

        ///
        /// Champs facultatifs
        if( property_exists($jsonData, 'login') ) $object->setLadapLogin($this->getFieldValue($jsonData, 'login'));

        // @TODO utiliser la méthode parent getFieldValue pour tous ces champs
        if( property_exists($jsonData, 'groups') ) $object->setLdapMemberOf($jsonData->groups);
        if( property_exists($jsonData, 'structure') ) $object->setLdapSiteLocation($jsonData->structure);
        if( property_exists($jsonData, 'affectation') ) $object->setLdapAffectation($jsonData->affectation);
        if( property_exists($jsonData, 'status') ) $object->setLdapStatus($jsonData->status);
        if( property_exists($jsonData, 'phone') ) $object->setPhone($jsonData->phone);
        if( property_exists($jsonData, 'inm') ) $object->setHarpegeINM($jsonData->inm);
        if( property_exists($jsonData, 'mail') ) $object->setEmail($jsonData->mail);


        if( $connectorName !== null )
            $object->setConnectorID($connectorName, $jsonData->uid);

        $object
            ->setFirstname($jsonData->firstname)
            ->setLastname($jsonData->lastname)
            ->setDateSyncLdap(new \DateTime())
        ;

        return $object;
    }
}