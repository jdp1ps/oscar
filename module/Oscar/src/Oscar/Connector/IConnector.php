<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-16 10:44
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use phpDocumentor\Reflection\Types\Mixed_;
use Laminas\ServiceManager\ServiceManager;

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
    function getParameter( string $key );

    /**
     * @param $key
     * @return bool
     */
    function hasParameter( string $key ) :bool ;

    /**
     * @param ServiceManager $sm Le ServiceManager de Zend
     * @param string $configPath L'emplacement du fichier de configuration YAML
     * @param string $shortName Le nom du connecteur dans la clef de configuration
     */
    public function init( ServiceManager $sm, string $configPath, string $shortName) :void ;

    /**
     * @param $optionName
     * @param null $defaultValue
     * @return mixed
     */
    public function getOption($optionName, $defaultValue=null);

    /**
     * Retourne le "chemin" vers les données complètes
     * @return string
     */
    public function getPathAll() :string;

    /**
     * Retourne le "chemin" vers la données correspondant à l'identifiant $remoteId
     * @param $remoteId
     * @return string
     */
    public function getPathSingle( $remoteId ) :string;
}