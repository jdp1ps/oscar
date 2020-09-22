<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/20
 * Time: 11:39
 */

namespace Oscar\Connector\DataAccessStrategy;


use Oscar\Connector\IConnectorOscar;

/**
 * Interface IDataAccessStrategy
 * @package Oscar\Connector\DataAccessStrategy
 * @deprecated
 */
interface IDataAccessStrategy
{
    /**
     * @param string $url
     * @return mixed
     * @deprecated
     */
    public function getData( string $url );

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

    public function getConnector() :IConnectorOscar;
}