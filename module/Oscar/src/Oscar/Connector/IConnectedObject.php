<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-23 12:06
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


interface IConnectedObject
{
    /**
     * Retourne la date de mise à jour.
     *
     * @return mixed
     */
    public function getDateUpdated();
}