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

        $output['id'] = $affectation->getId();
        $output['enrolledLabel'] = $affectation->getEnrolled()->__toString();
        $output['enrolled'] = $affectation->getEnrolled()->getId();
        $output['roleLabel'] = $affectation->getRoleObj()->getRoleId();
        $output['rolePrincipal'] = $affectation->getRoleObj()->isPrincipal();
        $output['roleId'] = $affectation->getRoleObj()->getId();
        $output['context'] = $context;

        $manage = false;
        $show = false;

        if( $this->oscarUserContext->hasPrivileges($manageEnrollPrivilege, $affectation->getEnroller()) ){
            $output['urlEdit'] = $this->url->fromRoute($this->getConf($affectation)->edit, ['idenroll' => $affectation->getId()]);
            $output['urlDelete'] = $this->url->fromRoute($this->getConf($affectation)->delete, ['idenroll' => $affectation->getId()]);
        }


//        if( $context == self::CONTEXT_PROJECT ){
//            if( $this->oscarUserContext->hasPrivileges($manageEnrollPrivilege, $affectation->getEnroller()) ){
//                $manage = true;
//                $output['urlEdit'] = $this->url->fromRoute('personproject/edit', ['idenroll' => $affectation->getId()]);
//                $output['urlDelete'] = $this->url->fromRoute('personproject/delete', ['idenroll' => $affectation->getId()]);
//            }
//        }
//        if( $context == self::CONTEXT_ACTIVITY ){
//            if( $this->oscarUserContext->hasPrivileges($manageEnrollPrivilege, $affectation->getEnroller()) ){
//                $manage = true;
//                $output['urlEdit'] = $this->url->fromRoute('personactivity/edit', ['idenroll' => $affectation->getId()]);
//                $output['urlDelete'] = $this->url->fromRoute('personactivity/delete', ['idenroll' => $affectation->getId()]);
//            }
//        }

        $output['manage'] = $manage;

        return $output;
    }

    const CONTEXT_PROJECT = "project";
    const CONTEXT_ACTIVITY = "activity";
    const CONTEXT_ORGANIZATION = "organization";


    private $_conf;
    private function getConf($affectation){
        if( $this->_conf === null ){
            $this->_conf = new \stdClass();
            switch (get_class($affectation) ){
                case ProjectPartner::class:
                    $this->_conf->context = self::CONTEXT_PROJECT;
                    $this->_conf->manage = Privileges::PROJECT_ORGANIZATION_MANAGE;
                    $this->_conf->show = Privileges::PROJECT_ORGANIZATION_SHOW;
                    $this->_conf->edit = 'organizationproject/edit';
                    $this->_conf->delete = 'organizationproject/delete';
                    break;
                case ProjectMember::class :
                    $this->_conf->context = self::CONTEXT_PROJECT;
                    $this->_conf->manage = Privileges::PROJECT_PERSON_MANAGE;
                    $this->_conf->show = Privileges::PROJECT_PERSON_SHOW;
                    $this->_conf->edit = 'personproject/edit';
                    $this->_conf->delete = 'personproject/delete';
                    break;
                case ActivityOrganization::class:
                    $this->_conf->context = self::CONTEXT_ACTIVITY;
                    $this->_conf->manage = Privileges::ACTIVITY_ORGANIZATION_MANAGE;
                    $this->_conf->show = Privileges::ACTIVITY_ORGANIZATION_SHOW;
                    $this->_conf->edit = 'organizationactivity/edit';
                    $this->_conf->delete = 'organizationactivity/delete';
                    break;
                case ActivityPerson::class :
                    $this->_conf->context = self::CONTEXT_ACTIVITY;
                    $this->_conf->manage = Privileges::ACTIVITY_PERSON_MANAGE;
                    $this->_conf->show = Privileges::ACTIVITY_PAYMENT_SHOW;
                    $this->_conf->edit = 'personactivity/edit';
                    $this->_conf->delete = 'personactivity/delete';
                    break;
                case OrganizationPerson::class:
                    $this->_conf->context = self::CONTEXT_ORGANIZATION;
                    $this->_conf->manage = Privileges::ORGANIZATION_EDIT;
                    $this->_conf->show = Privileges::ORGANIZATION_SHOW;
                    break;
                default:
                    throw new OscarException("Objet non-pris en charge");
            }
        }
        return $this->_conf;
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
