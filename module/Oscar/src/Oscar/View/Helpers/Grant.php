<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 02/03/16
 * Time: 16:18
 */

namespace Oscar\View\Helpers;


use Oscar\Service\OscarUserContext;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHtmlElement;

class Grant extends AbstractHtmlElement implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @return OscarUserContext
     */
    private function getOscarUserContext()
    {
        return $this->getServiceLocator()->getServiceLocator()->get('OscarUserContext');
    }

    public function connected()
    {
        return $this->getOscarUserContext()->getCurrentPerson() !== null;
    }

    public function isBoss(){
        return $this->connected() && $this->getOscarUserContext()->hasRolePrincipalInAnyOrganisations();
    }

    public function isDeclarer(){
        return $this->connected() && $this->getOscarUserContext()->isDeclarer();
    }

    public function getRolesPrincipauxPersonForActivity(){
        return $this->getOscarUserContext()->getRoleIdPrimary();
    }

    public function getRolesPrincipauxOrganization(){
        return $this->getOscarUserContext()->getRolesOrganisationLeader();
    }

    public function getSocketUrl(){
        $config = $this->getServiceLocator()->getServiceLocator()->get('Config');
        return $config['oscar']['socket']['url'];
    }

    public function hasSocket(){
        return (bool) $this->getServiceLocator()->getServiceLocator()->get('Config')['oscar']['socket'];
    }

    public function privileges()
    {
        return $this->getOscarUserContext()->getBasePrivileges();
    }

    public function privilege( $privilege, $resource = null )
    {
        return $this->getOscarUserContext()->hasPrivileges($privilege, $resource);
    }

    public function getPrivileges( $entity ){
        return $this->getOscarUserContext()->getPrivileges($entity);
    }

    public function role( $role  )
    {
        return $this->getOscarUserContext()->hasRole($role);
    }

    /**
     * @return array[]
     */
    public function getAllRoleIdPerson()
    {
        return $this->getOscarUserContext()->getAllRoleIdPerson();
    }

    /**
     * @return array[]
     */
    public function getAllRoleIdPersonInActivity()
    {
        return $this->getOscarUserContext()->getAllRoleIdPersonInActivity();
    }

    /**
     * @return array[]
     */
    public function getAllRoleIdOrganizationInActivity()
    {
        return $this->getOscarUserContext()->getRolesOrganizationInActivity();
    }

    public function ressourcePrivileges($r)
    {
        return $this->getOscarUserContext()->getPrivileges($r);
    }

    public function dbUser()
    {
        return $this->getOscarUserContext()->getDbUser();
    }

    /**
     * @return null|\Oscar\Entity\Person
     */
    public function getCurrentPerson(){
        return $this->getOscarUserContext()->getCurrentPerson();
    }


}
