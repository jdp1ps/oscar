<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 02/11/15 16:31
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\ProjectPartner;
use Oscar\Provider\Privileges;
use Oscar\Service\OscarUserContext;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\AbstractHtmlElement;

class Links extends AbstractHtmlElement implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public static $TLP_PROJECT = '<a href="%s" class="project"><i class="icon-cubes"></i>%s</a>';
    public static $TLP_ACTIVITY = '<a href="%s" class="activity"><i class="icon-cube"></i>%s</a>';
    public static $TLP_PERSON = '<a href="%s" class="person"><i class="%s"></i>&nbsp;%s</a>';
    public static $TLP_PERSON_UNLINK = '<strong date-href="%s" class="person"><i class="%s"></i>&nbsp;%s</strong>';
    public static $TLP_ORGANIZATION = '<a href="%s" class="organization %s"><i class="%s"></i>&nbsp;%s</a>';
    public static $TLP_ORGANIZATION_UNLINK = '<strong date-href="%s" class="organization link %s"><i class="%s"></i>&nbsp;%s</strong>';


    public static $TLP_MEMBER = '<a href="%s" %s class="member cartouche %s">%s<span class="addon">%s</span></a>';
    public static $TLP_MEMBER_UNLINK = '<span data-href="%s" %s class="member cartouche %s">%s<span class="addon">%s</span></span>';
    public static $TLP_PARTNER = '<a href="%s" class="cartouche organization %s" %s>%s<span class="addon">%s</span></a>';
    public static $TLP_PARTNER_UNLINK = '<span data-href="%s" class="cartouche organization %s" %s>%s<span class="addon">%s</span></span>';

    private $_personCache = [];
    private $_organizationCache = [];

    private $_partnerCache = [];
    private $_memberCache = [];

    /**
     * Affiche la listes des partenaires.
     *
     * @param $mixed
     * @return string
     */
    public function partners( $mixed, $css='' )
    {
        $out = [];
        if (is_array($mixed) || $mixed instanceof \Iterator || $mixed instanceof Collection) {
            foreach ($mixed as $o) {
                $out[] = $this->partner($o, $css);
            }
        } else {
            $out[] = $this->partner($mixed, $css);
        }
        return implode(',', $out);
    }

    /**
     * Affiche un partenaire sous la forme d'un cartouche.
     *
     * @param $organization
     * @return string
     */
    public function partner($organization, $css='')
    {
        if ($organization == null) {
            return '<i class="no-data">No data</i>';
        }
        if (!($organization instanceof ActivityOrganization || $organization instanceof ProjectPartner)) {
            return '<strong class="data-error">Invalid data</strong>';
        }

        if( !$organization->getOrganization() ){
            return '';
        }

        // Déplacement
        switch( $organization->getRole() ){
            case Organization::ROLE_LABORATORY :
                $css .= 'primary';
                break;
            case Organization::ROLE_COMPOSANTE_RESPONSABLE :
            case Organization::ROLE_COMPOSANTE_GESTION:
                $css .= 'secondary1';
                break;
            case Organization::ROLE_FINANCEUR:
                $css .= 'complementary';
                break;
            default:
                $css .= ' default';
        }

        if( $organization->isOutOfDate() ){
            $css .= ' obsolete ';
            if( $organization->isPast() ){
                $css .= ' past ';
            }
        }

        $tpl = self::$TLP_PARTNER_UNLINK;
        $data = '';
        $url = '';
        if( $this->getView()->grant()->privilege(Privileges::ORGANIZATION_SHOW) ){
            $tpl = self::$TLP_PARTNER;
            $url = $this->getView()->url('organization/show', ['id'=>$organization->getOrganization()->getId()]);
            $data = ' data-show="'.$url.'" ';
        }
        if( !$organization->getOrganization() ){
            return "";
        }
        $out = sprintf($tpl,
            $url,
            $css,
            $data,
            (string)$organization->getOrganization(),
            $organization->getRole()?:'Non-défini');


        return $this->_organizationCache[$organization->getId()] = $out;
    }

    public function organizations( $mixed )
    {
        $out = [];
        if (is_array($mixed) || $mixed instanceof \Iterator || $mixed instanceof Collection) {
            foreach ($mixed as $o) {
                $out[] = $this->organization($o);
            }
        } else {
            $out[] = $this->organization($mixed);
        }
        return implode(', ', $out);
    }

    public function organization($organization)
    {
        if ($organization == null) {
            return '<i class="no-data">No data</i>';
        }

        $icon = 'icon-building';
        if( $organization instanceof  ActivityOrganization ){
            $icon = 'icon-cube';
        }
        elseif ( $organization instanceof ProjectPartner ){
            $icon = 'icon-cubes';
        }

        if ($organization instanceof ActivityOrganization || $organization instanceof ProjectPartner) {
            $organization = $organization->getOrganization();
        }

        $class = " open";
        if( $organization->getDateEnd() && $organization->getDateEnd()->format('Y-m-d') < (new \DateTime())->format('Y-m-d')){
            $class = " obsolete";
        }

        if( isset($this->_organizationCache[$organization->getId()]) ){
            return $this->_organizationCache[$organization->getId()];
        }

        $tpl = self::$TLP_ORGANIZATION_UNLINK;
        if( $this->getView()->grant()->privilege(Privileges::ORGANIZATION_SHOW) ){
            $tpl = self::$TLP_ORGANIZATION;
        }

        if (!$organization instanceof Organization) {
            $out = '<strong class="data-error">Invalid data</strong>';
        } else {
            $out = sprintf($tpl, $this->getView()->url('organization/show', ['id' => $organization->getId()]), $class, $icon, (string)$organization);
        }

        return $this->_organizationCache[$organization->getId()] = $out;
    }

    public function project($project)
    {
        if ($project == null) {
            return '<i class="no-data">Pas de projet</i>';
        }

        if (!($project instanceof Project)) {
            return '<strong class="data-error">Invalid data</strong>';
        }

        return sprintf(self::$TLP_PROJECT, $this->getView()->url('project/show', ['id'=>$project->getId()]), (string)$project);
    }

    public function activity($activity, $project=true)
    {
        if ($activity == null) {
            return '<i class="no-data">ERROR</i>';
        }

        if (!($activity instanceof Activity)) {
            return '<strong class="data-error">Invalid data</strong>';
        }

        $out = '<span class="activity-span">';
        if( $project && $activity->getProject() ){
            $out .= sprintf('<a href="%s" title="%s" class="project-link"><i class="icon-cubes"></i> %s</a>',
                $this->getView()->url('project/show', ['id'=>$activity->getProject()->getId()]),
                $activity->getProject()->getLabel().' ' . $activity->getProject()->getDescription(),
                $activity->getProject()->getAcronym() ? $activity->getProject()->getAcronym() : (string)$activity->getProject()
            );
        }

        $out .= sprintf(self::$TLP_ACTIVITY, $this->getView()->url('contract/show', ['id'=>$activity->getId()]), (string)$activity);
        $out .= '</span>';

        return $out;
    }

    public function projectLink( Project $project, $css='' )
    {
        return sprintf('<a href="%s" class="project-link %s">%s</a>', $this->getView()->url('contract/show', ['id'=>$project->getId()]), $css, (string)$project);
    }

    public function members($dt, $css=''){
        return $this->personsRoled($dt, $css);
    }

    public function personsRoled($person, $css='')
    {
        $out = [];
        if (is_array($person) || $person instanceof \Iterator || $person instanceof Collection ) {
            foreach ($person as $p) {
                $out[] = $this->personRoled($p, $css);
            }
        } else {
            $out[] = $this->personRoled($person, $css);
        }
        return implode(',', $out);
    }

    /**
     * @param OrganizationPerson $affectation
     */
    public function affectationPerson( $affectation ){
        if( !$affectation->getPerson() || !$affectation->getOrganization() ){
            return '<span class="invalid-data">Données invalide</span>';
        }
        $css = 'cartouche-default';
        if( $affectation->getRoleObj() && $affectation->getRoleObj()->isPrincipal() ){
            $css .= ' primary';
        }
        return sprintf('<span class="cartouche organization %s">%s<span class="addon">%s</span></span>',
            $css,
            $this->organization($affectation->getOrganization()),
            $affectation->getRole());
    }

    /**
     * @param OrganizationPerson $affectation
     */
    public function organisationPerson( $affectation ){
        if( !$affectation->getPerson() || !$affectation->getOrganization() ){
            return '<span class="invalid-data">Données invalide</span>';
        }
        $css = 'cartouche-default';
        if( $affectation->idLeader() ){
            $css .= ' secondary1';
        }
        return sprintf('<span class="cartouche organization %s">%s<span class="addon">%s</span></span>',
            $css,
            $this->person($affectation->getPerson()),
            $affectation->getRole());
    }



    /**
     * Affichage d'un cartouche avec la personne et son rôle.
     *
     * @param $person
     * @return string
     */
    public function personRoled($person, $css='')
    {
        if ($person == null) {
            return '<i class="no-data">No data</i>';
        }
        if (!($person instanceof ActivityPerson || $person instanceof ProjectMember || $person instanceof OrganizationPerson)) {
            return '<strong class="data-error">Invalid data</strong>';
        }
        $css .= " cartouche-default";
        if( $person->isPrincipal() ){
            $css .= ' primary';
        }

        if( $person->isOutOfDate() ){
            $css .= ' obsolete ';
            if( $person->isPast() ){
                $css .= ' past ';
            }
        }

        $tpl = self::$TLP_MEMBER_UNLINK;
        $urlShow = '';
        $datas = '';

        if( $this->getView()->grant()->privilege(Privileges::PERSON_SHOW) ){
            $tpl = self::$TLP_MEMBER;
            $urlShow = $this->getView()->url('person/show', ['id'=>$person->getPerson()->getId()]);
            $datas .= ' data-show="'.$urlShow.'" ';
        }

        return sprintf($tpl, $urlShow, $datas,
            $css,
            $this->personName($person->getPerson()),
            $person->getRole());
    }


    public function personName(Person $person)
    {

        return ucwords(strtolower($person->getFirstname())).' '
            .strtoupper($person->getLastname())
            . ($person->getLdapAffectation() ?'<small> ('.$person->getLdapAffectation().')</small>':'');
    }

    public function persons($person)
    {
        $out = [];
        if (is_array($person) || $person instanceof \Iterator) {
            foreach ($person as $p) {
                $out[] = $this->person($p);
            }
        } else {
            $out[] = $this->person($person);
        }
        return implode(',', $out);
    }


    public function person($person)
    {

        if ($person == null) {
            return '<i class="no-data">No data</i>';
        }

        $class = 'icon-user';

        if ($person instanceof ActivityPerson ){
            $class = ' icon-cube';
        }
        if ($person instanceof ProjectMember ){
            $class = ' icon-cubes';
        }

        if ($person instanceof ActivityPerson || $person instanceof ProjectMember) {
            $person = $person->getPerson();
        }

        if (!$person instanceof Person) {
            $out = '<strong class="data-error">Invalid data</strong>';
        } else {
            $data = '';
            $tpl = self::$TLP_PERSON_UNLINK;
            if ($this->OscarUserContext()->hasPrivileges(Privileges::PERSON_SHOW) ){
                $tpl = self::$TLP_PERSON;
                $data = ' data-show="'. $this->getView()->url('person/show', ['id'=>$person->getId()]) .'"';
            }

            $out = sprintf($tpl, $this->getView()->url('person/show', ['id' => $person->getId()]), $class, $this->personName($person));
        }

        return $out;
    }

    public function __invoke( $thing=null )
    {
        if( $thing instanceof Activity ){
            return $this->activity($thing);
        }
        elseif( $thing instanceof Project ){
            return $this->project($thing);
        }
        return $this;
    }

    /**
     * @return OscarUserContext
     */
    private function OscarUserContext(){
        return $this->getServiceLocator()->getServiceLocator()->get('OscarUserContext');
    }
}
