<?php

namespace UnicaenCode\Service;

use UnicaenCode\Service\Traits\ConfigAwareTrait;
use UnicaenCode\Util;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Service\ViewHelperManagerFactory;

/**
 *
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class Introspection
{
    use ConfigAwareTrait;


    /**
     * Retourne la liste des services accessibles depuis le ServiceManager et correspondant aux critères suivants :
     * Les chaînes retournées sont les noms de classe des services
     *
     * @param string      $namespace Les classes retournées devront faire partie du namespace spécifié
     * @param null|string $rootClass Les classes retournées devront hériter de $rootClass si spécifié
     *
     * @return string[]
     */
    public function getInvokableServices($namespace, $rootClass = null)
    {
        if (substr($namespace, -1) !== '\\') {
            $namespace .= '\\';
        }

        $services = [];
        $smInvokables = $this->getServiceConfig()->getGlobalConfig()['service_manager']['invokables'];
        foreach ($smInvokables as $name => $sclass) {
            if (0 === strpos($sclass, $namespace)) {
                if (empty($rootClass) || is_subclass_of($sclass, $rootClass)) {
                    $services[$name] = $sclass;
                }
            }
        }

        return array_unique($services);
    }



    /**
     * Retourne la liste des aides de vues accessibles depuis le ServiceManager et correspondant aux critères suivants :
     * Les chaînes retournées sont les noms de classe des aides de vues
     *
     * @param string      $namespace Les classes retournées devront faire partie du namespace spécifié
     * @param null|string $rootClass Les classes retournées devront hériter de $rootClass si spécifié
     *
     * @return string[]
     */
    public function getViewHelpers($namespace = null, $rootClass = null)
    {
        if ($namespace && substr($namespace, -1) !== '\\') {
            $namespace .= '\\';
        }

        $viewHelpers = [];
        $vhm = Util::getServiceLocator()->get('viewHelperManager'); /* @var $vhm \Zend\View\HelperPluginManager */
        $registredServices = $vhm->getRegisteredServices();

        $vhs = $registredServices['invokableClasses'] + $registredServices['factories'] + $registredServices['aliases'];

        foreach ($vhs as $name) {
            $sclass = get_class( Util::getServiceLocator()->get('ViewHelperManager')->get($name) );
            if (! $namespace || 0 === strpos($sclass, $namespace)) {
                if (empty($rootClass) || is_subclass_of($sclass, $rootClass)) {
                    $viewHelpers[$name] = $sclass;
                }
            }
        }

        return array_unique($viewHelpers);
    }



    /**
     * Retourne la liste des hydrateurs accessibles depuis le ServiceManager et correspondant aux critères suivants :
     * Les chaînes retournées sont les noms de classe des hydrateurs
     *
     * @param string      $namespace Les classes retournées devront faire partie du namespace spécifié
     * @param null|string $rootClass Les classes retournées devront hériter de $rootClass si spécifié
     *
     * @return string[]
     */
    public function getInvokableHydrators($namespace, $rootClass = null)
    {
        if (substr($namespace, -1) !== '\\') {
            $namespace .= '\\';
        }

        $hydrateurs = [];
        $smInvokables = $this->getServiceConfig()->getGlobalConfig()['hydrators']['invokables'];
        foreach ($smInvokables as $name => $sclass) {
            if (0 === strpos($sclass, $namespace)) {
                if (empty($rootClass) || is_subclass_of($sclass, $rootClass)) {
                    $hydrateurs[$name] = $sclass;
                }
            }
        }

        return array_unique($hydrateurs);
    }



    /**
     * Retourne la liste des formulaires accessibles depuis le ServiceManager et correspondant aux critères suivants :
     * Les chaînes retournées sont les noms de classe des services
     *
     * @param string      $namespace Les classes retournées devront faire partie du namespace spécifié
     * @param null|string $rootClass Les classes retournées devront hériter de $rootClass si spécifié
     *
     * @return string[]
     */
    public function getInvokableForms($namespace, $rootClass = null)
    {
        if (substr($namespace, -1) !== '\\') {
            $namespace .= '\\';
        }

        $forms = [];
        $feInvokables = $this->getServiceConfig()->getGlobalConfig()['form_elements']['invokables'];
        foreach ($feInvokables as $name => $fclass) {
            if (0 === strpos($fclass, $namespace)) {
                if (empty($rootClass) || is_subclass_of($fclass, $rootClass)) {
                    $forms[$name] = $fclass;
                }
            }
        }

        return array_unique($forms);
    }



    /**
     * Retourne la liste des entités utilisées par doctrine dans l'application
     *
     * @return string[]
     */
    public function getDbEntities($namespace, $rootClass = null)
    {
        if (substr($namespace, -1) !== '\\') {
            $namespace .= '\\';
        }

        $entities = [];
        $metas    = Util::getEntityManager()->getMetadataFactory()->getAllMetadata();
        foreach ($metas as $meta) {
            $eclass = $meta->getName();
            if (0 === strpos($eclass, $namespace)) {
                if (empty($rootClass) || is_subclass_of($eclass, $rootClass)) {
                    $entities[] = $eclass;
                }
            }
        }
        sort($entities);

        return $entities;
    }



    /**
     * Retourne la liste des modules de l'application avec des informations associées
     *
     * Retourne un tableau associatif de la forme :
     *
     * nom du module => [
     *      name => string : nom du module,
     *      path => string : chemin d'accès du module,
     *      in-vendor => boolean : détermine si le module est dans /vendor ou non
     *      instance => instance du module (si nécessaire)
     * ]
     *
     * @param boolean $includeVendor inclue les modules du répertoire vendor ou non
     * @return array
     */
    public function getModules( $includeVendor = true )
    {
        $moduleManager = Util::getServiceLocator()->get('ModuleManager');
        /* @var $moduleManager ModuleManager */

        $appPath = getcwd();
        $modulesNames = $moduleManager->getModules();
        $modules = [];
        foreach( $modulesNames as $module ){
            $instance = $moduleManager->getModule($module);
            $rc = new \ReflectionClass( $instance );

            $path = dirname( $rc->getFileName() );
            $inVendor = 0 === strpos( str_replace($appPath.'/','',$path), 'vendor');

            if (!$inVendor || $includeVendor) {
                $modules[$module] = [
                    'name'      => $module,
                    'path'      => $path,
                    'in-vendor' => $inVendor,
                    //      'instance' => $instance
                ];
            }
        }

        return $modules;
    }
}