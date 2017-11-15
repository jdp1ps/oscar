<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-15 16:32
 * @copyright Certic (c) 2017
 */

namespace Oscar\Factory;


use Oscar\Entity\Person;

/**
 * Class JsonToPersonFactory
 * Cette classe permet de générer des objets Person à partir de données JSON.
 * @package Oscar\Factory
 */
class JsonToPersonFactory
{
    public function getInstance( $jsonData ){
        $person = new Person();
        $person->setFirstname($jsonData->firstname);
        return $person;
    }
}