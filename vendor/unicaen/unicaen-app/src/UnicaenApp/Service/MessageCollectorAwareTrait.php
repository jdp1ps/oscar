<?php

namespace UnicaenApp\Service;

use UnicaenApp\Exception\RuntimeException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Trait facilitant l'accès au service MessageCollector.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
trait MessageCollectorAwareTrait
{
    /**
     * @var MessageCollector
     */
    private $messageCollector;

    /**
     * Spécifie le service MessageCollector.
     *
     * @param MessageCollector $messageCollector
     * @return self
     */
    public function setServiceMessageCollector(MessageCollector $messageCollector = null)
    {
        $this->messageCollector = $messageCollector;
        
        return $this;
    }

    /**
     * Retourne le service MessageCollector.
     *
     * @return MessageCollector
     * @throws RuntimeException
     */
    public function getServiceMessageCollector()
    {
        if (null === $this->messageCollector) {
            if (! $this instanceof ServiceLocatorAwareInterface) {
                throw new RuntimeException("La classe " . get_class($this) . " n'a pas accès au ServiceLocator.");
            }

            $serviceLocator = $this->getServiceLocator();
            
            if ($serviceLocator instanceof ServiceLocatorAwareInterface) {
                $serviceLocator = $serviceLocator->getServiceLocator();
            }

            $this->messageCollector = $serviceLocator->get('MessageCollector');
        }
        
        return $this->messageCollector;
    }
}