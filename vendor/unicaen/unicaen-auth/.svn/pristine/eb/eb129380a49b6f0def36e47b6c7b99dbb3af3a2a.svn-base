<?php

namespace UnicaenAuth\Provider\Role;

use Doctrine\DBAL\DBALException;
use PDOException;
use BjyAuthorize\Provider\Role\ObjectRepositoryProvider;
use UnicaenAuth\Entity\Db\Role;

/**
 * Role provider based on a {@see \Doctrine\Common\Persistence\ObjectRepository}
 */
class DbRole extends ObjectRepositoryProvider
{
    /**
     * @var Role
     */
    protected $roles;



    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        if (null === $this->roles){
            try {
                $this->roles = parent::getRoles();
            }
            catch (DBALException $exc) {
                $this->roles = [];
            }
            catch (PDOException $exc) {
                $this->roles = [];
            }

            /* @var $roleObj \BjyAuthorize\Acl\Role */
            foreach ($this->roles as $roleObj) {
                if (!$roleObj->getParent()) {
                    $roleObj->setParent('user');
                }
            }

            return $this->roles;
        }
        return $this->roles;
    }
}