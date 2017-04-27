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

class MessageRepositoryFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MessageRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $messageConfig = $serviceLocator->get('MessageConfig'); /** @var MessageConfig $messageConfig */
        $messages      = Message::createInstancesFromConfig($messageConfig->getMessagesConfig());

        $service = new MessageRepository($messages);

        return $service;
    }
}