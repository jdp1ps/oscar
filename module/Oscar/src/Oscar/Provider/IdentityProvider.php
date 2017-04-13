<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 02/03/16
 * Time: 12:16
 */

namespace Oscar\Provider;


use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Provider\Identity\ChainableProvider;
use UnicaenAuth\Provider\Identity\ChainEvent;
use UnicaenAuth\Service\UserContext;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use BjyAuthorize\Provider\Identity\ProviderInterface as IdentityProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Gestion de l'identité courante dans Oscar.
 *
 * Class RoleProvider
 * @package Oscar\Provider
 */
class IdentityProvider implements ServiceLocatorAwareInterface, ChainableProvider, EntityManagerAwareInterface, IdentityProviderInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    function __construct()
    {
        die('YEAR');
    }

    public function injectIdentityRoles(ChainEvent $e)
    {
        $e->addRoles($this->getIdentityRoles());
    }

    /**
     * Retourne la liste des rôles de l'utilisateur courant.
     *
     * @return array
     */
    public function getIdentityRoles()
    {
        die('Passé par ici');
        /** @var UserContext $authService */
        $authService = $this->getServiceLocator()->get('authUserContext');

        $roles = ['user'];

        // Utilisateur LDAP
        if( ($ldap = $authService->getLdapUser()) ){
            // todo tester les filtres Ldap
        }

        // Rôles issus de la base de données
        elseif( $dbUser = $authService->getDbUser() ){
            foreach( $dbUser->getRoles() as $role ){
                $roles[] = $role->getRoleId();
            }
        }
        return $roles;
    }

}