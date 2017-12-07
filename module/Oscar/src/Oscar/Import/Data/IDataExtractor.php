<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-12-06 10:02
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Data;


interface IDataExtractor
{
    function extract( $data, $params = null );

    /**
     * Retourne un booléen indiquant si la dernière execution de la méthode extract a retournée une erreur.
     * @return boolean
     */
    function hasError();

    /**
     * Retourne le message d'erreur.
     * @return string|null
     */
    function getError();
}