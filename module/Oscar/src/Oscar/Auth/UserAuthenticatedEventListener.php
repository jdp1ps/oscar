<?php


namespace Oscar\Auth;


use Oscar\Entity\Authentification;
use UnicaenAuth\Event\Listener\AuthenticatedUserSavedAbstractListener;
use UnicaenAuth\Event\UserAuthenticatedEvent;

class UserAuthenticatedEventListener extends AuthenticatedUserSavedAbstractListener
{
    public function onUserAuthenticatedPrePersist(UserAuthenticatedEvent $e)
    {
        $auth = $e->getDbUser();
        if( $auth && get_class($auth) == Authentification::class ){
            die("ici");
        } else {
            die("LA");
        }
    }

}