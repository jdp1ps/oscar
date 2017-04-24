<?php

namespace UnicaenAuth\Assertion;

use BjyAuthorize\Service\Authorize;
use UnicaenAuth\Service\Traits\UserContextServiceAwareTrait;
use Zend\Mvc\Application;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Description of AbstractAssertion
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
abstract class AbstractAssertion implements AssertionInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * @var Acl
     */
    private $acl;

    /**
     * @var RoleInterface
     */
    private $role = false;

    /**
     * @var FlashMessenger
     */
    private $fm;



    /**
     * !!!! Pour éviter l'erreur "Serialization of 'Closure' is not allowed"... !!!!
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }



    /**
     * Returns true if and only if the assertion conditions are met
     *
     * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
     * $role, $this->resource, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
     * privileges, respectively.
     *
     * @param  Acl               $acl
     * @param  RoleInterface     $role
     * @param  ResourceInterface $resource
     * @param  string            $privilege
     *
     * @return bool
     */
    public final function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        $this->setAcl($acl);
        $this->setRole($role);
        $this->init();
        switch (true) {
            case $this->detectPrivilege($resource):

                return $this->assertPrivilege(ltrim(strstr($resource, '/'), '/'), $privilege);

            case $this->detectController($resource):

                $resource   = (string)$resource;
                $spos       = strpos($resource, '/') + 1;
                $dpos       = strrpos($resource, ':') + 1;
                $controller = substr($resource, $spos, $dpos - $spos - 1);
                $action     = substr($resource, $dpos);

                return $this->assertController($controller, $action, $privilege);

            case $this->detectEntity($resource):

                return $this->assertEntity($resource, $privilege);

            default:

                return $this->assertOther($resource, $privilege);
        }
    }



    /**
     * @param string|ResourceInterface $resource
     * @param string                   $privilege
     *
     * @return bool
     */
    public function isAllowed($resource, $privilege = null)
    {
        return $this->getServiceAuthorize()->isAllowed($resource, $privilege);
    }



    /**
     * @return Acl
     */
    public function getAcl()
    {
        if (!$this->acl){
            $this->acl = $this->getServiceAuthorize()->getAcl();
        }
        return $this->acl;
    }



    /**
     * @param Acl $acl
     *
     * @return AbstractAssertion
     */
    public function setAcl(Acl $acl = null)
    {
        $this->acl = $acl;

        return $this;
    }



    /**
     * @return RoleInterface
     */
    public function getRole()
    {
        if (false === $this->role){
            $sUserContext = $this->getServiceUserContext();
            if ($sUserContext->getIdentity()) {
                $this->role = $sUserContext->getSelectedIdentityRole();
            }
        }
        return $this->role;
    }



    /**
     * @param RoleInterface $role
     *
     * @return AbstractAssertion
     */
    public function setRole(RoleInterface $role = null)
    {
        $this->role = $role;

        return $this;
    }



    /**
     * @param string $resource
     *
     * @return boolean
     */
    private function detectPrivilege($resource = null)
    {
        if ($resource instanceof ResourceInterface) $resource = $resource->getResourceId();

        return is_string($resource) && 0 === strpos($resource, 'privilege/');
    }



    /**
     * @param string $privilege
     * @param string $subPrivilege
     *
     * @return boolean
     */
    protected function assertPrivilege($privilege, $subPrivilege = null)
    {
        return true;
    }



    /**
     * @param string $resource
     *
     * @return boolean
     */
    private function detectController($resource = null)
    {
        if ($resource instanceof ResourceInterface) $resource = $resource->getResourceId();

        return 0 === strpos($resource, 'controller/');
    }



    /**
     * Ititialisation des paramètres de l'assertion (si nécessaire)
     */
    public function init()
    {

    }



    /**
     * @param string $controller
     * @param string $action
     * @param string $privilege
     *
     * @return boolean
     */
    protected function assertController($controller, $action = null, $privilege = null)
    {
        return true;
    }



    /**
     * @param string $resource
     *
     * @return boolean
     */
    private function detectEntity($resource = null)
    {
        return
            is_object($resource)
            && method_exists($resource, 'getId');
    }



    /**
     * @param ResourceInterface $entity
     * @param string            $privilege
     *
     * @return boolean
     */
    protected function assertEntity(ResourceInterface $entity, $privilege = null)
    {
        return true;
    }



    /**
     * @param ResourceInterface $resource
     * @param string            $privilege
     *
     * @return boolean
     */
    protected function assertOther(ResourceInterface $resource = null, $privilege = null)
    {
        return true;
    }



    /**
     * @return MvcEvent
     */
    protected function getMvcEvent()
    {
        $application = $this->getServiceLocator()->get('Application');

        /* @var $application Application */

        return $application->getMvcEvent();
    }



    /**
     * @return Authorize
     */
    private function getServiceAuthorize()
    {
        $serviceAuthorize = $this->getServiceLocator()->get('BjyAuthorize\Service\Authorize');
        /* @var $serviceAuthorize Authorize */

        return $serviceAuthorize;
    }



    /**
     * @return FlashMessenger
     */
    protected function flashMessenger()
    {
        if (!$this->fm){
            $this->fm = $this->getServiceLocator()->get('controllerpluginmanager')->get('flashmessenger');
        }

        return $this->fm;
    }
}