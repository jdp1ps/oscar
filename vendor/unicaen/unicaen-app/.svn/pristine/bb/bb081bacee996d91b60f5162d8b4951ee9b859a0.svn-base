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

class MessageConfigFactory implements FactoryInterface
{
    private $serviceLocator;
    private $messageConfigNormalizer;

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MessageConfig
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        $appConfig = $serviceLocator->get('Config');
        $config    = isset($appConfig['message']) ? $appConfig['message'] : [];

        $normalizer = $this->getMessageConfigNormalizer()->setConfig($config);

        return MessageConfig::create($normalizer);
    }

    /**
     * @return MessageConfigNormalizer
     */
    public function getMessageConfigNormalizer()
    {
        if (null === $this->messageConfigNormalizer) {
            $this->messageConfigNormalizer = new MessageConfigNormalizer($this->serviceLocator);
        }
        return $this->messageConfigNormalizer;
    }

    /**
     * @param MessageConfigNormalizer $messageConfigNormalizer
     * @return $this
     */
    public function setMessageConfigNormalizer(MessageConfigNormalizer $messageConfigNormalizer)
    {
        $this->messageConfigNormalizer = $messageConfigNormalizer;
        return $this;
    }


}
