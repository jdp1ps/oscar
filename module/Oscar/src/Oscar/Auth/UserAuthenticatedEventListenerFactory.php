<?php


namespace Oscar\Auth;


use Laminas\ServiceManager\ServiceLocatorInterface;

class UserAuthenticatedEventListenerFactory
{
    public function __invoke( ServiceLocatorInterface $serviceLocator )
    {
        $listener = new UserAuthenticatedEventListener();
        return $listener;
    }
}