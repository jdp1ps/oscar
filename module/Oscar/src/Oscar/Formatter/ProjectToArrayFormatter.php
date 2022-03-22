<?php


namespace Oscar\Formatter;


use Oscar\Entity\ActivityDate;
use Oscar\Entity\Project;

class ProjectToArrayFormatter
{

    private $rolesPerson = [];
    private $rolesOrganization = [];
    private $milestoneTypes = [];



    public function configure( array $rolesPerson, array $rolesOrganization, array $milestoneTypes ) :void
    {
        $this->rolesPerson = $rolesPerson;
        $this->rolesOrganization = $rolesOrganization;
        $this->milestoneTypes = $milestoneTypes;

    }

    protected function setOptions( array $options ) :void
    {

    }

    public function format( Project $project ) :array
    {
        $output = [];

        $output['id'] = $project->getId();
        $output['acronym'] = $project->getAcronym();
        $output['label'] = $project->getLabel();
        $output['description'] = $project->getDescription();
        $output['absStart'] = "";
        $output['absEnd'] = "";

        $payementsDone = 0.0;
        $paymentsExpected = 0.0;
        $paymentsGap = 0.0;
        $paymentsExplain = [];

        $managementsFees = [];
        $managementsFeesHoster = [];

        $financialImpacts = [];
        $disciplines = [];

        // Données aggrégées
        $amount = 0.0;
        $pfi = [];
        $oscarNum = [];
        $type = [];
        $status = [];
        $start = [];
        $end = [];
        $signed = [];

        $persons = [];
        foreach ($this->rolesPerson as $role) {
            $persons[$role] = [];
        }
        foreach ($project->getPersonsDeep() as $personProject) {
            $person = (string) $personProject->getPerson();
            $role = $personProject->getRoleObj()->getRoleId();
            if( array_key_exists($role, $persons) && !in_array($person, $persons[$role]) ){
                $persons[$role][] = $person;
            }
        }

        $organizations = [];
        foreach ($this->rolesOrganization as $role) {
            $organizations[$role] = [];
        }
        foreach ($project->getOrganisationsDeep() as $organizationProject) {
            $organization = (string) $organizationProject->getOrganization();
            $role = $organizationProject->getRoleObj()->getRoleId();
            if( array_key_exists($role, $organizations) && !in_array($organization, $organizations[$role]) ){
                $organizations[$role][] = $organization;
            }
        }

        $milestones = [];
        foreach ($this->milestoneTypes as $t) {
            $milestones[$t] = [];
        }


        foreach ($project->getActivities() as $activity) {
            $amount += $activity->getAmount();

            if( $activity->getOscarNum() )
                $oscarNum[] = $activity->getOscarNum();

            if( $activity->getCodeEOTP() )
                $pfi[] = $activity->getCodeEOTP();

            if( $activity->getType() )
                $type[] = $activity->getType();

            $status[] = $activity->getStatusLabel();

            // Dates
            $startValue = $activity->getDateStartStr();
            $endValue = $activity->getDateEndStr();
            $signedValue = $activity->getDateSignedStr();

            if( $startValue && ($output['absStart'] > $startValue || $output['absStart'] == "") )
                $output['absStart'] = $startValue;

            if( $endValue && ($output['absEnd'] < $endValue || $output['absEnd'] == "") )
                $output['absEnd'] = $endValue;

            if( $startValue )
                $start[] = $startValue;

            if( $endValue )
                $end[] = $endValue;

            if( $signedValue )
                $signed[] = $signedValue;

            // Money
            $payementsDone += $activity->getTotalPaymentReceived();
            $paymentsExpected += $activity->getTotalPaymentProvided();
            $paymentsGap += $activity->getEcartPaiement();

            $paymentExplain = $activity->getEcartPaimentExplain();
            if( $paymentExplain )
                $paymentsExplain[] = $paymentExplain;

            if( $activity->getFraisDeGestionPartHebergeur() )
                $managementsFeesHoster[] = $activity->getFraisDeGestionPartHebergeur();

            if( $activity->getFraisDeGestion() )
                $managementsFees[] = $activity->getFraisDeGestion();

            $financialImpacts = [];

            if( $activity->getDisciplines() )
                $disciplines = array_merge($disciplines, $activity->getDisciplinesArray());

            //
            /** @var ActivityDate $m */
            foreach ($activity->getMilestones() as $m) {
                $type = $m->getType()->getLabel();
                $d = $m->getDateStartStr();

                if( $d ){
                    $milestones[$type][] = $d;
                }
            }
        }


        $output['amount'] = $amount;
        $output['pfi'] = implode(', ', $pfi);
        $output['oscarNum'] = implode(', ', $oscarNum);
        $output['type'] = implode(', ', $type);
        $output['status'] = implode(', ', $status);
        $output['start'] = implode(', ', $start);
        $output['end'] = implode(', ', $end);
        $output['signed'] = implode(', ', $signed);
        $output['paymentsDone'] = $payementsDone;
        $output['paymentsExpected'] = $paymentsExpected;
        $output['paymentsGap'] = $paymentsGap;
        $output['paymentsExplain'] = implode(', ', $paymentsExplain);
        $output['managementsFeesHoster'] = implode(', ', $managementsFeesHoster);
        $output['managementsFees'] = implode(', ', $managementsFees);
        $output['disciplines'] = implode(', ', array_unique($disciplines));

        foreach ($persons as $role=>$p) {
            $output[$role] = implode(', ', $p);
        }

        foreach ($organizations as $role=>$p) {
            $output[$role] = implode(', ', $p);
        }

        foreach ($milestones as $type=>$p) {
            $output[$type] = implode(', ', $p);
        }

        return $output;
    }

    public function headers() :array
    {
        return [
            "id" => "Id Project",
            "acronym" => "Acronyme",
            "label" => "Intitulé",
            "description" => "Description",
            "amount" => "Montant",
            "pfi" => "PFI",
            "oscarNum" => "N° Oscar",
            "type" => "Type(s)",
        ];
    }
}