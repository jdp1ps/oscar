<?php
namespace UnicaenAuth\Provider\Role;

use UnicaenApp\Mapper\Ldap\Group as Mapper;
use UnicaenAuth\Acl\NamedRole;

/**
 * Array config based Role provider.
 *
 * Les ajouts par rapport à la classe mère sont :
 * - l'instanciation de rôles "nommés" : la clé 'name' peut être ajoutée pour spécifier
 * le nom du rôle. Exemple :
 * <code>array('guest' => array('name' => "Profil de base", 'children' => array()))</code>
 * - la recherche du groupe LDAP dont le DN correspond au "role id" pour récupérer
 * son libellé (attribut "description"), s'il n'est pas déjà fourni dans la config.
 *
 * @see \BjyAuthorize\Provider\Role\Config
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class Config extends \BjyAuthorize\Provider\Role\Config
{
    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * @param Mapper $mapper
     * @param array $config
     */
    public function __construct(Mapper $mapper, array $config = [])
    {
        $this->mapper = $mapper;
        parent::__construct($config);
    }

    /**
     * @param string      $name
     * @param array       $options Ex: array('guest' => array('name' => "Profil de base", 'children' => array()))
     * @param string|null $parent
     *
     * @return array
     */
    protected function loadRole($name, $options = [], $parent = null)
    {
        if (isset($options['name'])) {
            $roleName = $options['name'];
        }
        elseif (ldap_explode_dn($name, 1) !== false && ($group = $this->mapper->findOneByDn($name))) {
            $roleName = $group->getDescription();
        }
        elseif (\UnicaenApp\Entity\Ldap\People::isSupannRoleEntite($name, $supann, $type, $code, $lib)) {
            $code = \UnicaenApp\Entity\Ldap\Structure::extractCodeStructureHarpege($code);
            $roleName = $lib . " ($supann, $code)";
        }
        else {
            $roleName = null;
        }

        if (isset($options['children']) && count($options['children']) > 0) {
            $children = $options['children'];
        }
        else {
            $children = [];
        }

        if (isset($options['description'])) {
            $description = (bool) $options['description'];
        }
        else {
            $description = false;
        }

        if (isset($options['selectable'])) {
            $selectable = (bool) $options['selectable'];
        }
        else {
            $selectable = true;
        }

        $roles   = [];
        $role    = new NamedRole($name, ($parent || $name === 'guest') ? $parent : 'user', $roleName, $description, $selectable);
        $roles[] = $role;

        foreach ($children as $key => $value) {
            if (is_numeric($key)) {
                $roles = array_merge($roles, $this->loadRole($value, [], $role));
            }
            else {
                $roles = array_merge($roles, $this->loadRole($key, $value, $role));
            }
        }

        return $roles;
    }
}