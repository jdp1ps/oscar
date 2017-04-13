<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-23 13:52
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;

interface IConnectorOrganization
{
    /**
     * Retourne un tableau contenant les données de la personne en passant
     * par l'ID côté tiers.
     *
     * @param $idConnector
     * @return array|null
     */
    function getOrganizationData( $idConnector );


    /**
     * Déclenche la synchronisation des personnes, mise à jour des personnes
     * existantes et création des nouvelles.
     *
     * @param PersonRepository $personRepository
     * @return mixed
     */
    function syncOrganizations( OrganizationRepository $organizationRepository, $force);

    /**
     * Récupére les informations de la personne depuis la source tiers et les
     * placent dans l'object Person passé en paramètre. Les changement ne sont
     * pas persistés.
     *
     * @param Person $person
     * @return mixed
     */
    function syncOrganization( Organization $organization );
}