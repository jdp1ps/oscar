<?php

namespace UnicaenApp\Message\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Description of AppLinkFactory
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class MessageHelperFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $helperPluginManager
     * @return AppInfos
     */
    public function createService(ServiceLocatorInterface $helperPluginManager)
    {
        $messageService = $helperPluginManager->getServiceLocator()->get('MessageService');

        return new MessageHelper($messageService);
    }
}