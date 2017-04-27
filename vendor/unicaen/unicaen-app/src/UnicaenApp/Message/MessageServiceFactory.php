<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 16/07/15
 * Time: 10:45
 */

namespace UnicaenApp\Message;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MessageServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MessageService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $messageRepository = $serviceLocator->get('MessageRepository'); /** @var MessageRepository $messageRepository */

        $service = new MessageService($messageRepository);

        return $service;
    }
}