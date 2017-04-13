<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-16 10:43
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;

interface IConnectorPerson extends IConnector
{
    /**
     * Retourne un tableau contenant les données de la personne en passant
     * par l'ID côté tiers.
     *
     * @param $idConnector
     * @return array|null
     */
    function getPersonData( $idConnector );


    /**
     * Déclenche la synchronisation des personnes, mise à jour des personnes
     * existantes et création des nouvelles.
     *
     * @param PersonRepository $personRepository
     * @param boolean $force
     * @return mixed
     */
    function syncPersons( PersonRepository $personRepository, $force );

    /**
     * Récupére les informations de la personne depuis la source tiers et les
     * placent dans l'object Person passé en paramètre. Les changement ne sont
     * pas persistés.
     *
     * @param Person $person
     * @return mixed
     */
    function syncPerson( Person $person );

}