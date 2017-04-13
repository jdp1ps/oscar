<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-08-25 11:32
 * @copyright Certic (c) 2016
 */

namespace Oscar\Provider;


interface IOrganizationProvider
{
    /**
     * Retourne les informations pour une personne (via l'id)
     *
     * @param $id
     * @return PersonProviderData
     */
    public function getOrganization( $id );

    /**
     * Retourne toutes les personnes.
     *
     * @param $id
     * @return PersonProviderData
     */
    public function getOrganizations();

    /**
     * Retourne le nom du provider.
     *
     * @return String
     */
    public function getName();
}