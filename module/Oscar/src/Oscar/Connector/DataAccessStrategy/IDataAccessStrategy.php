<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/20
 * Time: 11:39
 */

namespace Oscar\Connector\DataAccessStrategy;


use Oscar\Connector\IConnectorOscar;

interface IDataAccessStrategy
{
    public function getData( string $url );
    public function getConnector() :IConnectorOscar;
}