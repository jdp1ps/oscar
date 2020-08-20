<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-16 10:44
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


interface IConnector
{
    /**
     * Retourne le nom du connecteur tel que déclaré en clef dans
     * la configuration.
     *
     * @return mixed
     */
    function getName();

    /**
     * Retourne le nom du champ utilisé comme ID.
     *
     * @return mixed
     */
    function getRemoteID();


    /**
     * Retourne le nom du champ distant à partir du nom oscar.
     *
     * @param $oscarFieldName
     * @return mixed
     */
    function getRemoteFieldname( $oscarFieldName );

    /**
     * @param $key
     * @return mixed
     */
    function getParameter( $key );

    /**
     * @param $key
     * @return boolean
     */
    function hasParameter( $key );
}