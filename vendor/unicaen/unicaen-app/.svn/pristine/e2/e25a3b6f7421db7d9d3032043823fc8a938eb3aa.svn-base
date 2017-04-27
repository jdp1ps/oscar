<?php
namespace UnicaenApp\Service\Doctrine;

use DoctrineModule\Service\AbstractFactory;
use DoctrineModule\Service\DriverFactory;
use DoctrineModule\Service\EventManagerFactory;
use DoctrineModule\Service\Authentication\AdapterFactory;
use DoctrineModule\Service\Authentication\StorageFactory;
use DoctrineModule\Service\Authentication\AuthenticationServiceFactory;
use DoctrineORMModule\Service\ConfigurationFactory;
use DoctrineORMModule\Service\DBALConnectionFactory;
use DoctrineORMModule\Service\EntityManagerFactory;
use DoctrineORMModule\Service\EntityResolverFactory;
use DoctrineORMModule\Service\SQLLoggerCollectorFactory;
use DoctrineORMModule\Collector\MappingCollector;
use UnicaenApp\Exception\LogicException;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory abstraite permettant de simplifier la configuration de la connexion 
 * à plusieurs bases de données avec le module Doctrine ORM.
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MultipleDbAbstractFactory implements AbstractFactoryInterface
{
    const DOCTRINE_CONFIG_PREFIX = 'doctrine.';
    
    const SERVICE_TYPE_AUTHENTICATION_ADAPTER = 'authenticationadapter';
    const SERVICE_TYPE_AUTHENTICATION_STORAGE = 'authenticationstorage';
    const SERVICE_TYPE_AUTHENTICATION_SERVICE = 'authenticationservice';
    const SERVICE_TYPE_CONNECTION             = 'connection';
    const SERVICE_TYPE_CONFIGURATION          = 'configuration';
    const SERVICE_TYPE_ENTITYMANAGER          = 'entitymanager';
    const SERVICE_TYPE_DRIVER                 = 'driver';
    const SERVICE_TYPE_EVENTMANAGER           = 'eventmanager';
    const SERVICE_TYPE_ENTITY_RESOLVER        = 'entity_resolver';
    const SERVICE_TYPE_SQL_LOGGER_COLLECTOR   = 'sql_logger_collector';

    protected $serviceFactories = array();
    
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $requestedName = trim($requestedName);
        $types = array(
            self::SERVICE_TYPE_AUTHENTICATION_ADAPTER,
            self::SERVICE_TYPE_AUTHENTICATION_STORAGE,
            self::SERVICE_TYPE_AUTHENTICATION_SERVICE,
            self::SERVICE_TYPE_CONNECTION,
            self::SERVICE_TYPE_CONFIGURATION,
            self::SERVICE_TYPE_ENTITYMANAGER,
            self::SERVICE_TYPE_DRIVER,
            self::SERVICE_TYPE_EVENTMANAGER,
            self::SERVICE_TYPE_ENTITY_RESOLVER,
            self::SERVICE_TYPE_SQL_LOGGER_COLLECTOR,
        );
        $serviceType = $this->extractServiceType($requestedName);
        if (!$serviceType) {
            return false;
        }
        return in_array($serviceType, $types);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $requestedName = trim($requestedName);
        $serviceType = $this->extractServiceType($requestedName);
        $serviceName = $this->extractServiceName($requestedName);
        $factory = $this->getServiceFactory($serviceLocator, $serviceType, $serviceName);
        return $factory->createService($serviceLocator);
    }
    
    /**
     * Extrait le type de service.
     * 
     * @param string $requestedName
     * @return string
     */
    protected function extractServiceType($requestedName)
    {
        $parts = $this->extractRequestedNameParts($requestedName);
        return array_shift($parts);
    }
    
    /**
     * Extrait le nom de service.
     * 
     * @param string $requestedName
     * @return string
     */
    protected function extractServiceName($requestedName)
    {
        $parts = $this->extractRequestedNameParts($requestedName);
        return array_pop($parts);
    }
    
    /**
     * Extrait le type et le nom de service.
     * 
     * @param string $requestedName
     * @return array 0 => type, 1 => nom
     */
    protected function extractRequestedNameParts($requestedName)
    {
        if (0 !== strpos($requestedName, self::DOCTRINE_CONFIG_PREFIX)) {
            return array();
        }
        $requestedName = substr($requestedName, strlen(self::DOCTRINE_CONFIG_PREFIX));
        $parts = array_filter(explode('.', $requestedName));
        if (count($parts) !== 2) {
            return array();
        }
        return $parts;
    }
    
    /**
     * Instancie et retourne la factory à utiliser pour le type et le nom de service spécifiés.
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $serviceType
     * @param string $serviceName
     * @return AbstractFactory
     * @throws Exception
     */
    protected function getServiceFactory($serviceLocator, $serviceType, $serviceName)
    {
        if (!isset($this->serviceFactories[$serviceType])) {
            switch ($serviceType) {
                case self::SERVICE_TYPE_CONNECTION:
                    $factory = new DBALConnectionFactory($serviceName);
                    break;
                case self::SERVICE_TYPE_CONFIGURATION:
                    $factory = new ConfigurationFactory($serviceName);
                    break;
                case self::SERVICE_TYPE_ENTITYMANAGER:
                    $factory = new EntityManagerFactory($serviceName);
                    break;
                case self::SERVICE_TYPE_DRIVER:
                    $factory = new DriverFactory($serviceName);
                    break;
                case self::SERVICE_TYPE_EVENTMANAGER:
                    $factory = new EventManagerFactory($serviceName);
                    break;
                case self::SERVICE_TYPE_ENTITY_RESOLVER:
                    $factory = new EntityResolverFactory($serviceName);
                    break;
                case self::SERVICE_TYPE_SQL_LOGGER_COLLECTOR:
                    $factory = new SQLLoggerCollectorFactory($serviceName);
                    break;
                case self::SERVICE_TYPE_AUTHENTICATION_ADAPTER:
                    $factory = new AdapterFactory($serviceName);
                    break;
                case self::SERVICE_TYPE_AUTHENTICATION_STORAGE:
                    $factory = new StorageFactory($serviceName);
                    break;
                case self::SERVICE_TYPE_AUTHENTICATION_SERVICE:
                    $factory = new AuthenticationServiceFactory($serviceName);
                    break;
                default:
                    throw new LogicException("Type de service imprévu : '$serviceType'.");
                    break;
            }
    //                "DoctrineORMModule\Form\Annotation\AnnotationBuilder" => function(Zend\Di\ServiceLocatorInterface $sl) {
    //                    $service = new \Zend\Form\Annotation\AnnotationBuilder($sl->get("doctrine.entitymanager.$snort"));
    //                },
            $this->serviceFactories[$serviceType] = $factory;
        }
        return $this->serviceFactories[$serviceType];
    }
    
    /**
     * Force la factory à utiliser pour le type de service spécifié.
     * 
     * @param FactoryInterface $serviceFactory
     * @param string $serviceType Ex: 'connection', 'driver'
     * @return self
     */
    public function setServiceFactory(FactoryInterface $serviceFactory, $serviceType)
    {
        $this->serviceFactories[$serviceType] = $serviceFactory;
        return $this;
    }
}