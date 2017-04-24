<?php

namespace UnicaenApp\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHelper;

/**
 * Aide de vue donnant accès au collecteur de messages.
 * 
 * @see \UnicaenApp\Service\MessageCollector
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MessageCollectorHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    /**
     * Point d'entrée.
     * 
     * @return self
     */
    public function __invoke()
    {
        return $this;
    }
    
    /**
     * Code HTML.
     * 
     * @return string
     */
    public function __toString()
    {
        if (! $this->getService()->hasMessages()) {
            return '';
        }
        
        $messenger = clone $this->getView()->messenger(); /* @var $messenger Messenger */
        
        return (string) $messenger->setMessages($this->getService()->getMessages());
    }
    
    /**
     * Retourne le service collecteur de messages.
     * 
     * @return \UnicaenApp\Service\MessageCollector
     */
    private function getService()
    {
        return $this->getServiceLocator()->getServiceLocator()->get('MessageCollector');
    }
}