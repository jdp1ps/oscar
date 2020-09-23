<?php


namespace Oscar\Connector\Access;


use Oscar\Connector\IConnector;

interface IConnectorAccess
{
    /**
     * Retourne les données "brutes" PHP
     * @return mixed
     */
    public function getDatas( $url );

    /**
     * Retourne le connector
     * @return IConnector
     */
    public function getConnector() :IConnector;

    /**
     * Retourne les informations pour l'objet $remoteId.
     *
     * @param $remoteId
     * @param null $params
     * @return mixed
     */
    public function getDataSingle( $remoteId, $params=null );

    /**
     * Retourne toutes les informations.
     *
     * @param null $params
     * @return mixed
     */
    public function getDataAll( $params=null );
}