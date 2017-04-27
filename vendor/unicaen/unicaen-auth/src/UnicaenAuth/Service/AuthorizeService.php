<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link    https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

namespace UnicaenAuth\Service;

use UnicaenAuth\Service\Traits\UserContextServiceAwareTrait;

/**
 * Authorize service
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class AuthorizeService extends \BjyAuthorize\Service\Authorize
{
    use UserContextServiceAwareTrait;

    /**
     * Loading...
     *
     * @var boolean
     */
    protected $loading;



    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }



    /**
     * @deprecated this method will be removed in BjyAuthorize 1.4.x+,
     *             please retrieve the identity from the
     *             `BjyAuthorize\Provider\Identity\ProviderInterface` service
     *
     * @return string
     */
    public function getIdentity()
    {
        $this->loaded && $this->loaded->__invoke();
        if ($this->loading) return 'bjyauthorize-identity';

        // on retourne par défaut le rôle sélectionné
        $role = $this->getServiceUserContext()->getSelectedIdentityRole();
        if ($role) return $role;

        $roles = $this->getIdentityProvider()->getIdentityRoles();
        // sinon, si on est uniquement authentifié et que user est défini, on retourne le rôle user
        if (isset($roles['user'])) return $roles['user'];
        // sinon, si guest est défini alors on retourne guest
        if (isset($roles['guest'])) return $roles['guest'];

        // sinon rien du tout!!
        return null;
    }



    /**
     * Retourne la liste des rôles fournis par l'ensemble des providers
     */
    public function getRoles()
    {
        $roles = [];
        foreach ($this->roleProviders as $provider) {
            $r = $provider->getRoles();
            foreach ($r as $role) {
                $roles[$role->getRoleId()] = $role;
            }
        }

        return $roles;
    }



    /**
     * Initializes the service
     *
     * @internal
     *
     * @return void
     */
    public function load()
    {
        $this->loading = true;
        parent::load();
        $this->loading = false;
    }

}
