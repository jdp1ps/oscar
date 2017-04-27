<?php

namespace UnicaenAuth\Provider\Role;

use BjyAuthorize\Provider\Role\ProviderInterface;
use Zend\Authentication\AuthenticationService;
use ZfcUser\Entity\UserInterface;
use UnicaenAuth\Acl\NamedRole;

/**
 * Fournisseur de rôle retournant le rôle correspondant à l'identifiant de connexion
 * de d'utilisateur (username).
 *
 * Cela est utile lorsque l'on veut gérer les habilitations d'un utilisateur unique
 * sur des ressources.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Username implements ProviderInterface
{
    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $roles;

    /**
     *
     * @param AuthenticationService $authService
     * @param array $config
     */
    public function __construct(AuthenticationService $authService, $config = null)
    {
        $this->authService = $authService;
        $this->config      = $config;
    }

    /**
     * @return \Zend\Permissions\Acl\Role\RoleInterface[]
     */
    public function getRoles()
    {
        if (isset($this->config['enabled']) && !$this->config['enabled']) {
            return [];
        }

        if (null === $this->roles) {
            $this->roles = [];

            if ($this->authService->hasIdentity()) {
                $identity = $this->authService->getIdentity();
                if (isset($identity['ldap'])) {
                    $identity = $identity['ldap'];
                }
                elseif (isset($identity['db'])) {
                    $identity = $identity['db'];
                }
                if ($identity instanceof UserInterface) {
                    $role = new NamedRole($identity->getUsername(), 'user', "Authentifié(e)", null, false);
                    $this->roles[] = $role;
                }
            }
        }

        return $this->roles;
    }
}