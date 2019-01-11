<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 18-05-29 14:14
 * @copyright Certic (c) 2018
 */

namespace Oscar\Formatter;


use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\Project;
use Oscar\Entity\Role;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\OscarUserContext;

class JSONFormatter
{
    /**
     * @var OscarUserContext
     */
    private $oscarUserContext;

    /**
     * ActivityJSONFormatter constructor.
     * @param OscarUserContext $oscarUserContext
     */
    public function __construct(OscarUserContext $oscarUserContext)
    {
        $this->oscarUserContext = $oscarUserContext;
    }

    /**
     * @param $collection
     */
    public function objectsCollectionToJson( $collection, $compact = true ){
        $out = [];
        foreach ($collection as $item) {
            $out[] = $this->format($item, $compact);
        }
        return $out;
    }

    /**
     * @return OscarUserContext
     */
    protected function getOscarUserContext(){
        return $this->oscarUserContext;
    }

    public function formatProject( Project $project, $compact=true ){
        $data = [
            'id' => $project->getId(),
            'acronym' => $project->getAcronym(),
            'label' => $project->getLabel(),
            'description' => $project->getDescription(),
            'displayName' => (string) $project
        ];

        if( $compact === false ){
            // TODO Activités/Personnes/Organisations
        }

        return $data;
    }

    public function formatActivity( Activity $activity, $compact=true ){
        $datas = $activity->toArray();

        $datas['amount'] = [
            'value' => $activity->getAmount(),
            'currency' => $activity->getCurrency()->getSymbol()
        ];

        $datas['project'] = $activity->getProject() ? $this->formatProject($activity->getProject(), false) : null;
        $datas['project_id'] = $activity->getProject()?$activity->getProject()->getId():null;

        $datas['persons'] = null;
        $datas['persons_primary'] = null;

        if( $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_PERSON_SHOW, $activity) ) {

            $datas['persons'] = [];
            $datas['persons_primary'] = [];

            /** @var ActivityPerson $activityPerson */
            foreach ($activity->getPersonsDeep() as $activityPerson) {
                $role = $activityPerson->getRole();
                $person = [
                    'id' => $activityPerson->getPerson()->getId(),
                    'role' => $role,
                    'displayName' => $activityPerson->getPerson()->getDisplayName(),
                    'email' => $activityPerson->getPerson()->getEmail(),
                    'start' => $this->formatDateISO($activityPerson->getDateStart()),
                    'affectation' => $activityPerson->getPerson()->getLdapAffectation(),
                    'location' => $activityPerson->getPerson()->getLdapSiteLocation(),
                    'end' => $this->formatDateISO($activityPerson->getDateEnd()),
                    'spot' => get_class($activityPerson) == ActivityPerson::class ? 'activity' : 'project'
                ];

                if (!$activityPerson->getRoleObj()) {
                    error_log("ERREUR AVEC " . get_class($activityPerson) . $activityPerson->getId());
                }

                $principal = $activityPerson->getRoleObj() && $activityPerson->getRoleObj()->isPrincipal();

                if ($principal) {
                    if (!array_key_exists($role, $datas['persons_primary'])) {
                        $datas['persons_primary'][$role] = [];
                    }
                    $datas['persons_primary'][$role][] = $person;
                } else {
                    if (!array_key_exists($role, $datas['persons'])) {
                        $datas['persons'][$role] = [];
                    }
                    $datas['persons'][$role][] = $person;
                }
            }
        }

        $datas['organizations'] = [];
        $datas['organizations_primary'] = [];

        foreach ($activity->getOrganizationsDeep() as $activityOrganization) {
            $role = $activityOrganization->getRole();

            $organization = [
                'id' => $activityOrganization->getOrganization()->getId(),
                'role' => $role,
                'displayName' => (string)$activityOrganization->getOrganization(),
//                'email' => $activityOrganization->getOrganization()->getEmail(),
                'start' => $this->formatDateISO($activityOrganization->getDateStart()),
                'end' => $this->formatDateISO($activityOrganization->getDateEnd()),
                'spot' => get_class($activityOrganization) == ActivityOrganization::class ? 'activity' : 'project'
            ];

            $principal = $activityOrganization->getRoleObj() && $activityOrganization->getRoleObj()->isPrincipal();

            if( $principal ){
                if( !array_key_exists($role, $datas['organizations']) ){
                    $datas['organizations_primary'][$role] = [];
                }
                $datas['organizations_primary'][$role][] = $organization;
            } else {
                if( !array_key_exists($role, $datas['organizations']) ){
                    $datas['organizations'][$role] = [];
                }
                $datas['organizations'][$role][] = $organization;
            }
        }

        $datas['payments'] = [];
        /** @var ActivityPayment $payment */
        foreach ($activity->getPayments() as $payment) {
            $datas['payments'][] = [
                'amount' => $payment->getAmount(),
                'date' => $payment->getDatePayment() ? $payment->getDatePayment()->format('Y-m-d') : null,
                'predicted' => $payment->getDatePredicted() ? $payment->getDatePredicted()->format('Y-m-d') : null

            ];
        }

        $datas['milestones'] = [];
        /** @var ActivityDate $milestone */
        foreach ($activity->getMilestones() as $milestone) {
            $type = (string) $milestone->getType();
            $datas['milestones'][] = [
                'type' => $type,
                'date' => $milestone->getDateStart()->format('Y-m-d')
            ];
        }

        return $datas;
    }

    public function format($object, $compact = true){
        $class = get_class($object);
        switch ($class) {
            case Activity::class:
                return $this->formatActivity($object, $compact);

            case Project::class:
                return $this->formatProject($object, $compact);

            case Person::class:
                return $this->formatPerson($object, $compact);

            case Organization::class:
                return $this->formatOrganization($object, $compact);

            case OrganizationRole::class:
                return $object->toJson();

            case Role::class:
                return $object->toJson();

            default:
                throw new OscarException("Impossible de convertir $class ".OrganizationRole::class." au format JSON");
        }

    }

    public function formatDateISO( $data ){
        if( $data ){
            return $data->format('Y-m-d');
        } else {
            return null;
        }
    }


}