<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-08-25 10:32
 * @copyright Certic (c) 2016
 */

namespace Oscar\Provider;


interface IPersonProvider
{
    /**
     * Retourne les informations pour une personne (via l'id)
     *
     * @param $id
     * @return PersonProviderData
     */
    public function getPerson( $id );

    /**
     * Retourne toutes les personnes.
     *
     * @return PersonProviderData
     */
    public function getPersons();

    /**
     * Retourne le nom du provider.
     *
     * @return String
     */
    public function getName();

}