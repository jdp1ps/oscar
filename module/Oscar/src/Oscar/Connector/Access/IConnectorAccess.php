<?php


namespace Oscar\Connector\Access;


interface IConnectorAccess
{
    /**
     * Retourne les données "brutes" PHP
     * @return mixed
     */
    public function getDatas( $id = null);
}