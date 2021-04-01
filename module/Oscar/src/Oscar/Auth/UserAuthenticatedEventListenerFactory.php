<?php


namespace Oscar\Auth;


use Zend\ServiceManager\ServiceLocatorInterface;

class UserAuthenticatedEventListenerFactory
{
    public function __invoke( ServiceLocatorInterface $serviceLocator )
    {
        $listener = new UserAuthenticatedEventListener();
        return $listener;
    }
}