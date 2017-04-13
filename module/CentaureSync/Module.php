<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace CentaureSync;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
/*
    public function getConsoleBanner(AdapterInterface $console)
    {
        return "Centaure SYNC";
    }

    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            'sync persons [--verbose|-v]' => 'Synchronise les informations sur les personnes depuis LDAP.',
            [],
            ['--verbose|-v', 'Mode verbeux']
            ];
    }
*/
}
