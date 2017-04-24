<?php

namespace UnicaenLdap;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 *
 *
 * @author Laurent LECLUSE <laurent.lecluse at unicaen.fr>
 */
class Module implements ConfigProviderInterface, ServiceProviderInterface
{

    /**
     *
     * @return array
     * @see ConfigProviderInterface
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     *
     * @return array
     * @see AutoloaderProviderInterface
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     *
     * @return array
     * @see ServiceProviderInterface
     */
    public function getServiceConfig()
    {
        $services = array(
            'Generic', 'Group', 'People', 'Structure', 'System'
        );
        $processus = array(

        );
        $factories = array(
            'ldap' => 'UnicaenLdap\LdapFactory',
            'ldapOptions' => 'UnicaenLdap\Options\ModuleOptionsFactory',
        );
        foreach( $services as $service ){
            $factories['ldapService'.$service] = function($sm) use ($service){
                $className = 'UnicaenLdap\\Service\\'.$service;
                return new $className;
            };
        }
        foreach( $processus as $proc ){
            $factories['ldapProcessus'.$proc] = function($sm) use ($proc){
                $className = 'UnicaenLdap\\Processus\\'.$proc;
                return new $className;
            };
        }

        return array(
            'factories' => $factories,
        );
    }
}
