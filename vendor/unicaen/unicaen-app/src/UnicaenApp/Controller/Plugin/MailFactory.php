<?php

namespace UnicaenApp\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\Smtp;
use Zend\ServiceManager\Exception\InvalidArgumentException;

/**
 * Description of MailFactory
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MailFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $pluginManager
     * @return Mail
     */
    public function createService(ServiceLocatorInterface $pluginManager)
    {
        $options     = $pluginManager->getServiceLocator()->get('unicaen-app_module_options'); /* @var $options ModuleOptions */
        $mailOptions = $options->getMail();
        
        if (!isset($mailOptions['transport_options'])) {
            throw new InvalidArgumentException("Options de transport de mail introuvables.");
        }
        
        $transport = new Smtp(new SmtpOptions($mailOptions['transport_options']));
        $plugin    = new Mail($transport);
        
        if (isset($mailOptions['redirect_to'])) {
            $plugin->setRedirectTo($mailOptions['redirect_to']);
        }
        if (isset($mailOptions['do_not_send'])) {
            $plugin->setDoNotSend($mailOptions['do_not_send']);
        }
        
        return $plugin;
    }
}