<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 31/08/17
 * Time: 10:14
 */

namespace Oscar\Connector;


interface ConnectorInterface
{
    /**
     * Synchronise toute les informations.
     *
     * @return ConnectorRepport
     */
    public function syncAll();

    /**
     * Synchonise une information.
     *
     * @param $key Identifiant unique dans le source distante.
     * @return mixed
     */
    public function syncOne( $key );
}