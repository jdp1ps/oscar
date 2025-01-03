<?php


namespace Oscar\Formatter;


use Cocur\Slugify\Slugify;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\Project;

class ProjectToArrayFormatter implements IProjectFormater
{
    private array $rolesPerson = [];
    private array $rolesOrganization = [];
    private array $milestoneTypes = [];
    private array $numerotations = [];
    private string $dateFormat;
    private Slugify $slugger;

    const PREFIX_PERSON_ROLE = 'person';
    const PREFIX_ORGANIZATION_ROLE = 'organization';
    const PREFIX_MILESTONE = 'milestone';
    const PREFIX_NUMEROTATION = 'numerotation';


    protected function getNormalizedKey(string $label, string $prefix = '', string $suffix = ''): string
    {
        return
            ($prefix ? $prefix . '-' : '') .
            $this->slugger->slugify($label) .
            ($suffix ? '-' . $suffix : '');
    }

    protected function getPersonRoleKey(string $role): string
    {
        return $this->getNormalizedKey($role, self::PREFIX_PERSON_ROLE);
    }

    protected function getOrganizationRoleKey(string $role): string
    {
        return $this->getNormalizedKey($role, self::PREFIX_ORGANIZATION_ROLE);
    }

    protected function getMilestoneTypeKey(string $role): string
    {
        return $this->getNormalizedKey($role, self::PREFIX_MILESTONE);
    }

    protected function getNumerotationKey(string $num): string
    {
        return $this->getNormalizedKey($num, self::PREFIX_NUMEROTATION);
    }

    public function configure(
        array $rolesPerson,
        array $rolesOrganization,
        array $milestoneTypes,
        array $numerotations,
        string $dateFormat = 'Y-m-d'
    ): void {
        $this->rolesPerson = $rolesPerson;
        $this->rolesOrganization = $rolesOrganization;
        $this->milestoneTypes = $milestoneTypes;
        $this->numerotations = $numerotations;
        $this->slugger = new Slugify();
        $this->dateFormat = $dateFormat;
    }

    public function format(Project $project): array
    {
        $output = [];
        foreach ($this->headers() as $key => $label) {
            $output[$key] = '';
        }

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

        $numerotations = [];
        foreach ($this->numerotations as $num) {
            $numerotations[$this->getNumerotationKey($num)] = [];
        }

        // Données aggrégées
        $amount = 0.0;
        $pfi = [];
        $oscarNum = [];
        $types = [];
        $status = [];
        $start = [];
        $end = [];
        $signed = [];

        $persons = [];
        foreach ($this->rolesPerson as $role) {
            $persons[$role] = [];
        }
        foreach ($project->getPersonsDeep() as $personProject) {
            $person = (string)$personProject->getPerson();
            $role = $personProject->getRoleObj()->getRoleId();
            if (array_key_exists($role, $persons) && !in_array($person, $persons[$role])) {
                $persons[$role][] = $person;
            }
        }

        $organizations = [];
        foreach ($this->rolesOrganization as $role) {
            $organizations[$role] = [];
        }
        foreach ($project->getOrganisationsDeep() as $organizationProject) {
            $organization = (string)$organizationProject->getOrganization();
            if( $organizationProject->getRoleObj() ){
                $role = $organizationProject->getRoleObj()->getRoleId();
                if (array_key_exists($role, $organizations) && !in_array($organization, $organizations[$role])) {
                    $organizations[$role][] = $organization;
                }
            }
        }

        $milestones = [];
        foreach ($this->milestoneTypes as $t) {
            $milestones[$t] = [];
        }

        foreach ($project->getActivities() as $activity) {
            $amount += $activity->getAmount();

            if ($activity->getOscarNum()) {
                $oscarNum[] = $activity->getOscarNum();
            }

            if ($activity->getCodeEOTP()) {
                $pfi[] = $activity->getCodeEOTP();
            }

            if ($activity->getActivityType()) {
                $types[] = $activity->getActivityType()->getLabel();
            }

            $status[] = $activity->getStatusLabel();

            // Dates
            $startValue = $activity->getDateStartStr($this->dateFormat);
            $endValue = $activity->getDateEndStr($this->dateFormat);
            $signedValue = $activity->getDateSignedStr($this->dateFormat);

            if ($startValue && ($output['absStart'] > $startValue || $output['absStart'] == "")) {
                $output['absStart'] = $startValue;
            }

            if ($endValue && ($output['absEnd'] < $endValue || $output['absEnd'] == "")) {
                $output['absEnd'] = $endValue;
            }

            if ($startValue) {
                $start[] = $startValue;
            }

            if ($endValue) {
                $end[] = $endValue;
            }

            if ($signedValue) {
                $signed[] = $signedValue;
            }

            // Money
            $payementsDone += $activity->getTotalPaymentReceived();
            $paymentsExpected += $activity->getTotalPaymentProvided();
            $paymentsGap += $activity->getEcartPaiement();

            $paymentExplain = $activity->getEcartPaimentExplain();
            if ($paymentExplain) {
                $paymentsExplain[] = $paymentExplain;
            }

            if ($activity->getFraisDeGestionPartHebergeur()) {
                $managementsFeesHoster[] = $activity->getFraisDeGestionPartHebergeur();
            }

            if ($activity->getFraisDeGestion()) {
                $managementsFees[] = $activity->getFraisDeGestion();
            }

            if ($activity->getFinancialImpact()) {
                $financialImpacts[] = $activity->getFinancialImpact();
            }

            if ($activity->getDisciplines()) {
                $disciplines = array_merge($disciplines, $activity->getDisciplinesArray());
            }

            //
            /** @var ActivityDate $m */
            foreach ($activity->getMilestones() as $m) {
                $type = $m->getType()->getLabel();
                $d = $m->getDateStartStr($this->dateFormat);

                if ($d) {
                    $milestones[$type][] = $d;
                }
            }

            foreach ($this->numerotations as $num) {
                $keyNum = $this->getNumerotationKey($num);
                $value = $activity->getNumber($num);
                if ($value) {
                    $numerotations[$keyNum][] = $value;
                }
            }
        }

        $output['amount'] = $amount;
        $output['pfi'] = implode(', ', $pfi);
        $output['oscarNum'] = implode(', ', $oscarNum);
        $output['types'] = implode(', ', array_unique($types));
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
        $output['financialImpacts'] = implode(', ', $financialImpacts);
        $output['disciplines'] = implode(', ', array_unique($disciplines));

        foreach ($persons as $role => $p) {
            $output[$this->getPersonRoleKey($role)] = implode(', ', $p);
        }

        foreach ($organizations as $role => $p) {
            $output[$this->getOrganizationRoleKey($role)] = implode(', ', $p);
        }

        foreach ($milestones as $type => $p) {
            $output[$this->getMilestoneTypeKey($type)] = implode(', ', $p);
        }

        foreach ($numerotations as $key => $numbers) {
            $output[$key] = implode(', ', $numbers);
        }

        return $output;
    }

    /**
     * Création des Clefs/En-têtes
     *
     * @return string[]
     */
    public function headers(): array
    {
        static $headers;

        if ($headers === null) {
            $headers = [
                "id" => "Id Project",
                "acronym" => "Acronyme",
                "label" => "Intitulé",
                "description" => "Description",
                "absStart" => "Début Abs",
                "absEnd" => "Fin Abs",
                'amount' => "Montant",
                'pfi' => "N°Financier",
                'oscarNum' => "N° Oscar",
                'types' => "Types",
                'status' => "Statuts",
                'start' => "Débuts",
                'end' => "Fins",
                'signed' => "Signatures",
                'paymentsDone' => "Versements effectués",
                'paymentsExpected' => "Versements prévus",
                'paymentsGap' => "écarts de paiements",
                'paymentsExplain' => "Info versement",
                'managementsFeesHoster' => "Part hébergeur",
                'managementsFees' => "Frais de gestion",
                'financialImpacts' => "Impact financier",
                'disciplines' => "Disciplines",
            ];

            foreach ($this->rolesPerson as $role) {
                $headers[$this->getPersonRoleKey($role)] = $role;
            }

            foreach ($this->rolesOrganization as $role) {
                $headers[$this->getOrganizationRoleKey($role)] = $role;
            }

            foreach ($this->milestoneTypes as $t) {
                $headers[$this->getMilestoneTypeKey($t)] = $t;
            }

            foreach ($this->numerotations as $n) {
                $headers[$this->getNumerotationKey($n)] = $n;
            }
        }

        return $headers;
    }

    public function formatProject(Project $project)
    {
        return $this->format($project);
    }
}