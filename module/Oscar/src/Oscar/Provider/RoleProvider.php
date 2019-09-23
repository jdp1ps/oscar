<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/03/16 14:06
 * @copyright Certic (c) 2016
 */

namespace Oscar\Provider;


use BjyAuthorize\Provider\Role\ProviderInterface;
use Doctrine\ORM\EntityManager;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenAuth\Acl\NamedRole;
use UnicaenAuth\Entity\Db\Role;
use UnicaenAuth\Service\UserContext;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Gestion des rôles dans Oscar.
 *
 * Class RoleProvider
 * @package Oscar\Provider
 */
class RoleProvider implements ProviderInterface
{
    /** @var EntityManager  */
    private $em;

    function __construct( EntityManager $em ){
        $this->em = $em;
    }

    public function getRoles()
    {
        static $_roles;

        if ($_roles === null) {
            $_roles = $this->buildRoles();
        }
        return $_roles;
    }

    private function buildRoles()
    {
        // Liste des rôles
        $roles = [];

        // Rôles chargés en base de données
        $rolesDB = $this->em->getRepository(Role::class)->findAll();

        // Création des utilisateurs par défaut (User et Guest)
        $roles['guest'] = $this->getNamedRole('guest');
        $roles['user'] = $this->getNamedRole('user');
        $roles['user']->setParent($this->getNamedRole('guest'));

        /** @var Role $role */
        foreach ($rolesDB as $role) {
            $roles[$role->getRoleId()] = $this->getNamedRole($role->getRoleId(), $role->getParent() ? $role->getParent()->getRoleId() : 'user');
        }

        return $roles;
    }

    /**
     * @param $roleId Identifiant du rôle
     * @param $parent Identifiant du rôle parent
     * @return NamedRole
     */
    private function getNamedRole( $roleId, $parent=null )
    {
        static $_cacheUsers = [];

        if( !isset($_cacheUsers[$roleId]) ){

            $created = new \BjyAuthorize\Acl\Role($roleId);

            // Si le rôle n'est ni 'guest' ni 'user' et qu'il n'a pas de parent,
            // on lui attribut 'user' comme parent.
            if( $roleId !== 'user' && $roleId !== 'guest' && $parent === null ){
                $parent = 'user';
            }

            if( $parent !== null ){
                $created->setParent($this->getNamedRole($parent));
            }

            $_cacheUsers[$roleId] = $created;
        }


        return $_cacheUsers[$roleId];
    }
}