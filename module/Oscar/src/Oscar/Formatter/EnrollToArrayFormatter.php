<?php


namespace Oscar\Formatter;


use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\OscarUserContext;
use \Zend\Mvc\Controller\Plugin\Url;

class EnrollToArrayFormatter
{
    private $oscarUserContext;
    private $url;

    /**
     * EnrollToArrayFormatter constructor.
     * @param OscarUserContext $oscarUserContext
     */
    public function __construct(OscarUserContext $oscarUserContext, Url $url)
    {
        $this->oscarUserContext = $oscarUserContext;
        $this->url = $url;
    }


    public function format( $affectation, ?array $options = null, ?array $output = null) :array
    {
        if( $output == null ){
            $output = [];
        }

        if( $options == null ){
            $options = [];
        }

        $manageEnrollPrivilege = $this->getManagePrivilege($affectation);
        $showEnrolledPrivilege = $this->getShowEnrolledPrivilege($affectation);
        $context = $this->getContext($affectation);

        if( $affectation->getRoleObj() == null ){
            throw new OscarException(sprintf("%s n'a pas d'objet rôle sur %s.",
                                             $affectation->getEnrolled(),
                                             $affectation->getEnroller()));
        }

        $output['id'] = $affectation->getId();
        $output['enrolledLabel'] = $affectation->getEnrolled()->__toString();
        $output['enrolled'] = $affectation->getEnrolled()->getId();
        $output['roleLabel'] = $affectation->getRoleObj()->getRoleId();
        $output['rolePrincipal'] = $affectation->getRoleObj()->isPrincipal();
        $output['roleId'] = $affectation->getRoleObj()->getId();
        $output['context'] = $context;
        $output['contextKey'] = '';
        if( $context == 'activity' ){
            $output['contextKey'] = $affectation->getEnroller()->getOscarNum();
        }
        if( $context == 'project' ){
            $output['contextKey'] = $affectation->getEnroller()->getAcronym();
        }

        $manage = false;
        $show = false;

        if( $this->oscarUserContext->hasPrivileges($manageEnrollPrivilege, $affectation->getEnroller()) ){
            $output['urlEdit'] = $this->url->fromRoute($this->getConf($affectation)->edit, ['idenroll' => $affectation->getId()]);
            $output['urlDelete'] = $this->url->fromRoute($this->getConf($affectation)->delete, ['idenroll' => $affectation->getId()]);
        }

        $output['manage'] = $manage;

        return $output;
    }

    const CONTEXT_PROJECT = "project";
    const CONTEXT_ACTIVITY = "activity";
    const CONTEXT_ORGANIZATION = "organization";


    private $_conf;
    private function getConf($affectation){
        $_conf = new \stdClass();
        switch (get_class($affectation) ){
            case ProjectPartner::class:
                $_conf->context = self::CONTEXT_PROJECT;
                $_conf->manage = Privileges::PROJECT_ORGANIZATION_MANAGE;
                $_conf->show = Privileges::PROJECT_ORGANIZATION_SHOW;
                $_conf->edit = 'organizationproject/edit';
                $_conf->delete = 'organizationproject/delete';
                break;
            case ProjectMember::class :
                $_conf->context = self::CONTEXT_PROJECT;
                $_conf->manage = Privileges::PROJECT_PERSON_MANAGE;
                $_conf->show = Privileges::PROJECT_PERSON_SHOW;
                $_conf->edit = 'personproject/edit';
                $_conf->delete = 'personproject/delete';
                break;
            case ActivityOrganization::class:
                $_conf->context = self::CONTEXT_ACTIVITY;
                $_conf->manage = Privileges::ACTIVITY_ORGANIZATION_MANAGE;
                $_conf->show = Privileges::ACTIVITY_ORGANIZATION_SHOW;
                $_conf->edit = 'organizationactivity/edit';
                $_conf->delete = 'organizationactivity/delete';
                break;
            case ActivityPerson::class :
                $_conf->context = self::CONTEXT_ACTIVITY;
                $_conf->manage = Privileges::ACTIVITY_PERSON_MANAGE;
                $_conf->show = Privileges::ACTIVITY_PAYMENT_SHOW;
                $_conf->edit = 'personactivity/edit';
                $_conf->delete = 'personactivity/delete';
                break;
            case OrganizationPerson::class:
                $_conf->context = self::CONTEXT_ORGANIZATION;
                $_conf->manage = Privileges::ORGANIZATION_EDIT;
                $_conf->show = Privileges::ORGANIZATION_SHOW;
                break;
            default:
                throw new OscarException("Objet non-pris en charge");
        }

        return $_conf;
    }

    protected function getManagePrivilege($affectation) {
        return $this->getConf($affectation)->manage;
    }

    protected function getShowEnrolledPrivilege($affectation){
        return $this->getConf($affectation)->show;
    }

    protected function getContext($affectation) :string {
        return $this->getConf($affectation)->context;
    }
}
