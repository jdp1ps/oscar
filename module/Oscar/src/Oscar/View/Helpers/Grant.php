<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 02/03/16
 * Time: 16:18
 */

namespace Oscar\View\Helpers;


use Oscar\Entity\ContractDocument;
use Oscar\Service\OscarUserContext;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Laminas\View\Helper\AbstractHtmlElement;

class Grant extends AbstractHtmlElement implements UseOscarUserContextService
{
    use UseOscarUserContextServiceTrait;

    public function connected()
    {
        return $this->getOscarUserContextService()->getCurrentPerson() !== null;
    }

    public function isBoss(){
        return $this->connected() && $this->getOscarUserContextService()->hasRolePrincipalInAnyOrganisations();
    }

    /**
     * Retourne TRUE si la personne connectée est un référent (N+1)
     *
     * @return boolean
     */
    public function hasPersonnelAccess(){
        return $this->connected() && $this->getOscarUserContextService()->hasPersonnelAccess();
    }


    public function isDeclarer(){
        return $this->connected() && $this->getOscarUserContextService()->isDeclarer();
    }

    public function getRolesPrincipauxPersonForActivity(){
        return $this->getOscarUserContextService()->getRoleIdPrimary();
    }

    public function getRolesPrincipauxOrganization(){
        return $this->getOscarUserContextService()->getRolesOrganisationLeader();
    }

    public function hasDocumentAccess( ContractDocument $contractDocument ):bool
    {
        return $this->getOscarUserContextService()->getAccessDocument($contractDocument)['read'] === true;
    }

    public function hasSignaturePersonalAccess():bool
    {
        return $this->getOscarUserContextService()->hasSignaturePersonalAccess();
    }

    /**
     * @deprecated
     * @return string
     */
    public function getSocketUrl(){
        return '';
    }

    /**
     * @deprecated
     * @return bool
     */
    public function hasSocket(){
        return false;
    }

    public function privileges()
    {
        return $this->getOscarUserContextService()->getBasePrivileges();
    }

    public function privilege( $privilege, $resource = null )
    {
        return $this->getOscarUserContextService()->hasPrivileges($privilege, $resource);
    }

    public function privilegeDeep( $privilege, $resource = null )
    {
        return $this->getOscarUserContextService()->hasPrivilegeDeep($privilege);
    }

    public function hasPrivilegeInOrganizations($privilege){
        return $this->getOscarUserContextService()->hasPrivilegeInOrganizations($privilege);
    }

    public function getPrivileges( $entity ){
        return $this->getOscarUserContextService()->getPrivileges($entity);
    }

    public function role( $role  )
    {
        return $this->getOscarUserContextService()->hasRole($role);
    }

    /**
     * @return array[]
     */
    public function getAllRoleIdPerson()
    {
        return $this->getOscarUserContextService()->getAllRoleIdPerson();
    }

    /**
     * @return array[]
     */
    public function getAllRoleIdPersonInActivity()
    {
        return $this->getOscarUserContextService()->getAllRoleIdPersonInActivity();
    }

    /**
     * @return array[]
     */
    public function getAllRoleIdOrganizationInActivity()
    {
        return $this->getOscarUserContextService()->getRolesOrganizationInActivity();
    }

    public function ressourcePrivileges($r)
    {
        return $this->getOscarUserContextService()->getPrivileges($r);
    }

    public function dbUser()
    {
        return $this->getOscarUserContextService()->getDbUser();
    }

    /**
     * @return null|\Oscar\Entity\Person
     */
    public function getCurrentPerson(){
        return $this->getOscarUserContextService()->getCurrentPerson();
    }
}
