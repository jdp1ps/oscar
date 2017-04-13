<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-23 12:03
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;

/**
 * Les repository utilisés pour persister les objets connectés oscar doivent
 * implémenter cette interface.
 *
 * Class IConnectedRepository
 * @package Oscar\Connector
 */
interface IConnectedRepository
{
    public function getObjectByConnectorID( $connectorName, $connectorID);
    public function newPersistantObject();
    public function flush( $mixed );
}