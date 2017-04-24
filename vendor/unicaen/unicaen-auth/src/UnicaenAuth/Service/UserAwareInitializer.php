<?php

namespace UnicaenAuth\Service;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use UnicaenAuth\Service\DbUserAwareInterface;
use UnicaenAuth\Service\LdapUserAwareInterface;
use ZfcUser\Entity\UserInterface;
use UnicaenAuth\Entity\Ldap\People;

/**
 * Initialisateur chargé d'injecter l'utilisateur courant dans les services en ayant besoin.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class UserAwareInitializer implements InitializerInterface
{
    /**
     * Test d'éligibilité.
     * 
     * @param mixed $instance
     * @return bool
     */
    protected function canInitialize($instance)
    {
        return $instance instanceof DbUserAwareInterface || $instance instanceof LdapUserAwareInterface;
    }
    
    /**
     * Initialize
     *
     * @param mixed $instance
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        // test d'éligibilité à faire au plus tôt pour éviter l'erreur
        // 'Circular dependency for LazyServiceLoader was found for instance Zend\Authentication\AuthenticationService'
        if (!$this->canInitialize($instance)) {
            return;
        }
        
        $authenticationService = $serviceLocator->get('Zend\Authentication\AuthenticationService');
        if (!$authenticationService->hasIdentity()) {
            return;
        }

        $identity = $authenticationService->getIdentity();
        
        if ($instance instanceof DbUserAwareInterface) {
            if (isset($identity['db']) && $identity['db'] instanceof UserInterface) {
                $instance->setDbUser($identity['db']);
            }
        }
        
        if ($instance instanceof LdapUserAwareInterface) {
            if (isset($identity['ldap']) && $identity['ldap'] instanceof People) {
                $instance->setLdapUser($identity['ldap']);
            }
        }
    }
}