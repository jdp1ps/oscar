<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/20
 * Time: 11:02
 */

namespace Oscar\Connector;


use Zend\ServiceManager\ServiceManager;

interface IConnectorOscar
{
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
     * Retourne la valeur du paramêtre (Requis).
     *
     * @param string $parameterName
     * @return mixed
     */
    public function getParameter( string $parameterName );

    /**
     * Retourn le nom du connecteur (unique si utilisation de plusieurs connecteurs)
     * @return mixed
     */
    public function getName();
}