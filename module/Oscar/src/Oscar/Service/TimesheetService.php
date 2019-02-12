<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\Authentification;
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
use Oscar\Entity\Referent;
use Oscar\Entity\TimeSheet;
use Oscar\Entity\TimesheetRepository;
use Oscar\Entity\ValidationPeriod;
use Oscar\Entity\ValidationPeriodRepository;
use Oscar\Entity\WorkPackage;
use Oscar\Entity\WorkPackagePerson;
use Oscar\Exception\OscarCredentialException;
use Oscar\Exception\OscarException;
use Oscar\Formatter\TimesheetsMonthFormatter;
use Oscar\Provider\Privileges;
use Oscar\Utils\ConfigurationMergable;
use Oscar\Utils\DateTimeUtils;
use Oscar\Utils\DateTimeUtilsTest;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Form\Element\Time;
use Zend\Log\Logger;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Gestion des Personnes :
 *  - Collaborateurs
 *  - Membres de projet/organisation.
 */
class TimesheetService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;

    /**
     * Retourne la configuration OSCAR.
     *
     * @return ConfigurationParser
     */
    protected function getOscarConfig()
    {
        return $this->getServiceLocator()->get('OscarConfig');
    }

    /**
     * Supprimer la Person $person de  la validation.
     *
     * @param $type prj|sci|adm
     * @param Person $person
     * @param ValidationPeriod $validation
     * @throws OscarException
     */
    public function removeValidatorToValidation($type, Person $person, ValidationPeriod $validation)
    {
        switch ($type) {
            case 'prj' :
                $validation->getValidatorsPrj()->removeElement($person);
                break;
            case 'sci' :
                $validation->getValidatorsSci()->removeElement($person);
                break;
            case 'adm' :
                $validation->getValidatorsAdm()->removeElement($person);
                break;
            default:
                throw new OscarException('Type de validation inconnu');
        }
        $this->getEntityManager()->flush($validation);
    }

    /**
     * Ajoute la Person $person à la validation.
     *
     * @param $type
     * @param Person $person
     * @param ValidationPeriod $validation
     * @throws OscarException
     */
    public function addValidatorToValidation($type, Person $person, ValidationPeriod $validation)
    {
        switch ($type) {
            case 'prj' :
                $validation->addValidatorPrj($person);
                break;
            case 'sci' :
                $validation->addValidatorSci($person);
                break;
            case 'adm' :
                $validation->addValidatorAdm($person);
                break;
            default:
                throw new OscarException('Type de validation inconnu');
        }
        $this->getEntityManager()->flush($validation);
        $this->notificationsValidationPeriod($validation);
    }


    public function changePersonSchedulePeriod($person, $days, $period){
        try {
            $periodDatas = DateTimeUtils::extractPeriodDatasFromString($period);
            $declarations = $this->getEntityManager()->getRepository(ValidationPeriod::class)->createQueryBuilder('d')
                ->where('d.declarer = :person AND d.year = :year AND d.month = :month')
                ->setParameters([
                    'person' => $person,
                    'year' => $periodDatas['year'],
                    'month' => $periodDatas['month'],
                ])
                ->getQuery()
                ->getResult();

            /** @var ValidationPeriod $declaration */
            foreach ($declarations as $declaration) {
                $schedule = $declaration->getSchedule();
                $scheduleJson = json_decode($schedule, JSON_OBJECT_AS_ARRAY);
                if( !$scheduleJson ){
                    $scheduleJson = [];
                }
                $scheduleJson['days'] = $days;
                $declaration->setSchedule(json_encode($scheduleJson));
            }

            $this->getEntityManager()->flush($declarations);
        } catch( \Exception $e ){
            throw new OscarException($e->getMessage());
        }
    }


    public function getDatasDeclarations()
    {
        $output = [];
        $declarations = $this->getEntityManager()->getRepository(ValidationPeriod::class)->findAll();
        /** @var ValidationPeriod $declaration */
        foreach ($declarations as $declaration) {
            $period = sprintf('%s-%s', $declaration->getYear(), $declaration->getMonth());
            $personId = $declaration->getDeclarer()->getId();
            $dataKey = sprintf('%s_%s', $period, $personId);

            if (!array_key_exists($dataKey, $output)) {
                $output[$dataKey] = [
                    'key' => $dataKey,
                    'period' => $period,
                    'person' => (string)$declaration->getDeclarer(),
                    'person_id' => $declaration->getDeclarer()->getId(),
                    'settings' => $declaration->getSchedule(),
                    'declarations' => [],
                ];
            }

            $object = $declaration->getObject();

            if ($object == ValidationPeriod::OBJECT_ACTIVITY) {
                /** @var Activity $activity */
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($declaration->getObjectId());
                $label = (string)$activity->getFullLabel();

            } else {

                $label = $this->getOthersWPByCode($object)['label'];
            }

            $declarationDatas = $declaration->toJson();
            $declarationDatas['label'] = $label;
            $declarationDatas['person'] = (string)$declaration->getDeclarer();
            $declarationDatas['period'] = $period;
            $declarationDatas['validation'] = $declaration->getState();

            $output[$dataKey]['declarations'][] = $declarationDatas;
        }
        return $output;
    }

    //////////////////////////////////////////////////////////////////////// VALIDATION des PERIODES
    public function validationProject(ValidationPeriod $validationPeriod, Person $validator, $message = '')
    {
        if ($validationPeriod->getStatus() !== ValidationPeriod::STATUS_STEP1) {
            throw new OscarException("Erreur d'état");
        }

        $person = (string)$validator;

        $validationPeriod->addLog("Validation projet", $person);
        $validationPeriod->setStatus(ValidationPeriod::STATUS_STEP2);
        $validationPeriod->setValidationActivityAt(new \DateTime())
            ->setValidationActivityBy((string)$validator)
            ->setValidationActivityById($validator->getId())
            ->setValidationActivityMessage($message);

        $this->getEntityManager()->flush($validationPeriod);

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// SYSTEME DE VALIDATION
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function validationSci(ValidationPeriod $validationPeriod, Person $validator, $message = '')
    {
        if ($validationPeriod->getStatus() !== ValidationPeriod::STATUS_STEP2) {
            throw new OscarException("Erreur d'état");
        }

        $person = (string)$validator;
        $validationPeriod->addLog("Validation scientifique par", $person);

        $validationPeriod->setStatus(ValidationPeriod::STATUS_STEP3);
        $validationPeriod->setValidationSciAt(new \DateTime())
            ->setValidationSciBy((string)$validator)
            ->setValidationSciById($validator->getId())
            ->setValidationSciMessage($message);


        $this->getEntityManager()->flush($validationPeriod);

        return true;
    }


    public function validationAdm(ValidationPeriod $validationPeriod, Person $validator, $message = '')
    {

        if ($validationPeriod->getObjectGroup() == ValidationPeriod::GROUP_OTHER) {
            if (!in_array($validationPeriod->getStatus(), [ValidationPeriod::STATUS_STEP1, ValidationPeriod::STATUS_STEP1, ValidationPeriod::STATUS_STEP3])) {
                throw new OscarException("Vous ne pouvez pas valider cette période (erreur de status - " . $validationPeriod->getStatus() . ").");
            }
        } else if ($validationPeriod->getStatus() !== ValidationPeriod::STATUS_STEP3) {
            throw new OscarException("Erreur d'état, la période doit être validée scientifiquement avant.");
        }

        $person = (string)$validator;
        $date = new \DateTime();

        $validationPeriod->addLog("Validation administrative par", $person);
        $validationPeriod->setStatus(ValidationPeriod::STATUS_VALID);
        $validationPeriod->setValidationAdmAt($date)
            ->setValidationAdmBy((string)$validator)
            ->setValidationAdmById($validator->getId())
            ->setValidationAdmMessage($message);

        $this->getEntityManager()->flush($validationPeriod);

        return true;
    }

    public function rejectPrj(ValidationPeriod $validationPeriod, Person $validator, $message = '')
    {
        if ($validationPeriod->getStatus() !== ValidationPeriod::STATUS_STEP1) {
            throw new OscarException("Erreur d'état");
        }

        $log = $validationPeriod->getLog();
        $person = (string)$validator;
        $date = new \DateTime();
        $msg = $date->format('Y-m-d H:i:s') . " : Rejet PROJET par $person\n";
        $log .= $msg;

        $validationPeriod->setLog($log);
        $validationPeriod->setStatus(ValidationPeriod::STATUS_CONFLICT);
        $validationPeriod->setRejectActivityAt($date)
            ->setRejectActivityBy((string)$validator)
            ->setRejectActivityById($validator->getId())
            ->setRejectActivityMessage($message);

        $this->getEntityManager()->flush($validationPeriod);

        return true;
    }

    public function rejectSci(ValidationPeriod $validationPeriod, Person $validator, $message = '')
    {
        if ($validationPeriod->getStatus() !== ValidationPeriod::STATUS_STEP2) {
            throw new OscarException("Erreur d'état");
        }

        $log = $validationPeriod->getLog();
        $person = (string)$validator;
        $date = new \DateTime();
        $msg = $date->format('Y-m-d H:i:s') . " : Rejet SCIENTIFIQUE par $person\n";
        $log .= $msg;

        $validationPeriod->setLog($log);
        $validationPeriod->setStatus(ValidationPeriod::STATUS_CONFLICT);
        $validationPeriod->setRejectSciAt($date)
            ->setRejectSciBy((string)$validator)
            ->setRejectSciById($validator->getId())
            ->setRejectSciMessage($message);

        $this->getEntityManager()->flush($validationPeriod);

        return true;
    }

    public function rejectAdm(ValidationPeriod $validationPeriod, Person $validator, $message = '')
    {
        if ($validationPeriod->getObjectGroup() == ValidationPeriod::GROUP_OTHER) {
            if (!in_array($validationPeriod->getStatus(), [ValidationPeriod::STATUS_STEP1, ValidationPeriod::STATUS_STEP1, ValidationPeriod::STATUS_STEP3])) {
                throw new OscarException("Vous ne pouvez pas rejeter cette période (erreur de status - " . $validationPeriod->getStatus() . ").");
            }
        } else if ($validationPeriod->getStatus() !== ValidationPeriod::STATUS_STEP3) {
            throw new OscarException("Erreur d'état, la période doit être validée scientifiquement avant.");
        }

        $log = $validationPeriod->getLog();
        $person = (string)$validator;
        $date = new \DateTime();
        $msg = $date->format('Y-m-d H:i:s') . " : Rejet ADMINISTRATIF par $person\n";
        $log .= $msg;

        $validationPeriod->setLog($log);
        $validationPeriod->setStatus(ValidationPeriod::STATUS_CONFLICT);
        $validationPeriod->setRejectAdmAt($date)
            ->setRejectAdmBy((string)$validator)
            ->setRejectAdmById($validator->getId())
            ->setRejectAdmMessage($message);

        $this->getEntityManager()->flush($validationPeriod);

        return true;
    }

    /**
     * Réenvoi une déclaration en conflit.
     *
     * @param ValidationPeriod $validationPeriod
     * @return bool
     * @throws OscarException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function reSendValidation(ValidationPeriod $validationPeriod)
    {
        if ($validationPeriod->getStatus() !== ValidationPeriod::STATUS_CONFLICT) {
            throw new OscarException("Erreur d'état");
        }

        $validationPeriod->addLog('Réenvoi de la déclaration pour validation', (string)$validationPeriod->getDeclarer());
        $validationPeriod->setStatus(ValidationPeriod::STATUS_STEP1);

        // Reset des champs
        $validationPeriod->setRejectSciBy(null)->setRejectSciMessage('')->setRejectSciAt(null)->setRejectSciById(null)
            ->setRejectAdmBy(null)->setRejectAdmMessage('')->setRejectAdmAt(null)->setRejectAdmById(null)
            ->setRejectActivityBy(null)->setRejectActivityMessage('')->setRejectActivityAt(null)->setRejectActivityById(null);

        $this->getEntityManager()->flush($validationPeriod);
        $this->notificationsValidationPeriod($validationPeriod);

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getValidationPeriodPersonConflict(Person $person)
    {
        $out = [];
        $validationsPeriods = $this->getValidationPeriodRepository()->getValidationPeriodPersonWithConflict($person->getId());
        foreach ($validationsPeriods as $validationsPeriod) {
            $activity = null;
            if ($validationsPeriod->getObject() == ValidationPeriod::OBJECT_ACTIVITY) {
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($validationsPeriod->getObjectId());
            }
            $out[] = [
                'validation' => $validationsPeriod,
                'activity' => $activity
            ];
        }
        return $out;
    }

    public function getValidationPeriodsOutWPToValidate($person = null)
    {
        return $this->getValidationPeriodRepository()->getValidationPeriodsOutWPToValidate($person ? $person->getId() : null);
    }

    public function getValidationPeriodsOutWP($person = null)
    {
        return $this->getValidationPeriodRepository()->getValidationPeriodsOutWP($person ? $person->getId() : null);
    }


    /**
     * @return OscarUserContext
     */
    protected function getOscarUserContext()
    {
        return $this->getServiceLocator()->get('OscarUserContext');
    }

    /**
     * @return ValidationPeriodRepository
     */
    protected function getValidationPeriodRepository()
    {
        return $this->getEntityManager()->getRepository(ValidationPeriod::class);
    }

    public function getTimesheetToValidateByOrganization(Organization $organization)
    {
        // Activities

        $query = $this->getEntityManager()->createQueryBuilder()->select('ts')
            ->from(TimeSheet::class, 'ts')
            ->innerJoin('ts.activity', 'a')
            ->innerJoin('a.project', 'p')
            ->leftJoin('a.organizations', 'o1')
            ->leftJoin('o1.roleObj', 'roleo1')
            ->leftJoin('p.partners', 'o2')
            ->leftJoin('o2.roleObj', 'roleo2')
            ->where('ts.status = :status')
            ->andWhere('(o1.organization = :organization AND roleo1.principal = true) OR (o2.organization = :organization AND roleo2.principal = true)')
            ->setParameters([
                'status' => TimeSheet::STATUS_TOVALIDATE,
                'organization' => $organization
            ]);
        return $query->getQuery()->getResult();
    }


    /**
     * Retourne les créneaux de la personne regroupès par activité
     * @param Person $person
     */
    public function getPersonTimesheetsCSV(Person $person, Activity $activity, $validatedOnly = false)
    {
        $fmt = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'Europe/Paris',
            \IntlDateFormatter::GREGORIAN,
            'd MMMM Y');

        $query = $this->getEntityManager()->getRepository(TimeSheet::class)
            ->createQueryBuilder('t')
            ->where('t.person = :person')
            ->andWhere('t.activity = :activity')
            ->orderBy('t.activity, t.dateFrom');

        $datas = [];

        $workpackages = [];

        /** @var WorkPackage $workPackage */
        foreach ($activity->getWorkPackages() as $workPackage) {
            $workpackages[] = $workPackage->getCode();
        }
        sort($workpackages);

        $ligneHeader = [(string)$person];
        foreach ($workpackages as $wpCode) {
            $ligneHeader[] = $wpCode;
        }

        $ligneHeader[] = "Commentaires";
        $ligneHeader[] = "Nbr créneaux";
        $ligneHeader[] = "Total jour";

        $datas[] = $ligneHeader;

        $days = [];
        $yearStart = (int)$activity->getDateStart()->format('Y');
        $yearEnd = (int)$activity->getDateEnd()->format('Y');

        // Prétraitement des créneaux
        $creneaux = [];
        $timesheets = $query->getQuery()->setParameters([
            'person' => $person,
            'activity' => $activity
        ])->getResult();

        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {

            $dayStr = $fmt->format($timesheet->getDateFrom());
            $code = $timesheet->getWorkpackage()->getCode();
            $comment = trim($timesheet->getComment());
            $duration = (float)$timesheet->getDuration();

            if (!array_key_exists($dayStr, $creneaux)) {
                $creneaux[$dayStr] = [];
                foreach ($workpackages as $wpCode) {
                    $creneaux[$dayStr][$wpCode] = 0.0;
                }
                $creneaux[$dayStr]['total'] = 0.0;
                $creneaux[$dayStr]['qte'] = 0;
                $creneaux[$dayStr]['commentaire'] = [];
            }

            $creneaux[$dayStr][$code] += $duration;
            $creneaux[$dayStr]['commentaire'][] = $comment;
            $creneaux[$dayStr]['total'] += $duration;
            $creneaux[$dayStr]['qte'] += 1;
        }


        $content = [];

        foreach ($creneaux as $dayStr => $dayData) {
            $ligne = [$dayStr];
            foreach ($workpackages as $wpCode) {
                $ligne[] = $creneaux[$dayStr][$wpCode];
            }
            $ligne[] = implode(" - ", array_unique($creneaux[$dayStr]['commentaire']));
            $ligne[] = $creneaux[$dayStr]['qte'];
            $ligne[] = $creneaux[$dayStr]['total'];
            $datas[] = $ligne;
        }

        return $datas;
    }

    /**
     * Permet de savoir si la période peut être soumise à validation
     * @param Person $person
     * @param $month
     * @param $year
     */
    public function periodSubmitable(Person $person, $month, $year)
    {
        return count($this->getValidationPeriods($year, $month, $person)) == 0;
    }


    public function validatePersonPeriod($person, $year, $month)
    {
        throw new \Exception("Non implémenté");
    }


    /**
     * Retourne la liste des ValidationPeriod pour une activité donnée.
     *
     * @param Activity $activity
     * @return array
     */
    public function getValidationPeriodsActivity(Activity $activity)
    {

        $query = $this->getEntityManager()->getRepository(ValidationPeriod::class)
            ->createQueryBuilder('v')
            ->where('v.object = :object AND v.object_id = :idactivity')
            ->setParameters([
                'idactivity' => $activity->getId(),
                'object' => 'activity',
            ])
            ->addOrderBy('v.year', 'DESC')->addOrderBy('v.month', 'DESC');

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne la procédure de validation pour l'activité à la période donnée
     *
     * @param Activity $activity
     * @return array
     */
    public function getValidationPeriodActivityAt(Activity $activity, Person $person, $year, $month)
    {
        return $this->getValidationPeriodRepository()->getValidationPeriodForActivity($year, $month, $activity->getId(), $person->getId());
    }

    public function getOthersWP()
    {
        static $others;
        if ($others == null) {
            $others = $this->getOscarConfig()->getConfiguration('horslots');
        }
        return $others;
    }

    public function getOthersWPByCode($code)
    {
        $conf = $this->getOthersWP();
        if (array_key_exists($code, $conf)) {
            return $conf[$code];
        }
        return [
            'code' => 'invalid',
            'label' => $code . ' (invalide)',
            'description' => 'Créneaux érroné',
            'icon' => true,
        ];
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// ACCÉS aux CONFIGURATION HORAIRES
    ///

    /**
     * @param $idPeriod
     * @return null|ValidationPeriod
     */
    public function getValidationPeriod($idPeriod)
    {
        return $this->getValidationPeriodRepository()->find($idPeriod);
    }


    public function getStatusMessage( $status ){
        static $statusMessages;
        if( $statusMessages === null ){
            $statusMessages = [
                ValidationPeriod::STATUS_VALID => "Validée",
                ValidationPeriod::STATUS_CONFLICT => "Conflict",
                ValidationPeriod::STATUS_STEP1 => "Validation projet",
                ValidationPeriod::STATUS_STEP2 => "Validation scientifique",
                ValidationPeriod::STATUS_STEP3 => "Validation administrative",
            ];
        }
        if( array_key_exists($status, $statusMessages) ){
            return $statusMessages[$status];
        } else {
            return "Unknow Status";
        }
    }



    public function getValidationsForValidator2(Person $validator, $declarer = null, $period = null)
    {
        $timesheetFormatter = new TimesheetsMonthFormatter();

        // Configuration des Hors-Lots
        $hwp = $this->getOthersWP();

        // Récupération des données à traiter
        $queryValidationPeriod = $this->getEntityManager()->getRepository(ValidationPeriod::class)
            ->createQueryBuilder('vp')
            ->leftJoin('vp.validatorsPrj', 'vprj')
            ->leftJoin('vp.validatorsSci', 'vsci')
            ->leftJoin('vp.validatorsAdm', 'vadm')
            ->where("vprj = :person OR vsci = :person OR vadm = :person")
            ->setParameter('person', $validator)
            ->getQuery();

        $periods = $queryValidationPeriod->getResult();

        $group = [];
        $periodIds = [];
        $output = [];
        $hasValidableStep = false;

        /** @var ValidationPeriod $period */
        foreach ($periods as $period) {
            $periodIds[] = $period->getId();
            $key = $period->getPeriod().'-'.$period->getDeclarer()->getId();
            $validateCurrentState = false;
            $validators = [];
            foreach ( $period->getCurrentValidators() as $v ){
                if( $v->getId() == $validator->getId() ){
                    $validateCurrentState = true;
                }
                $validators[] = (string)$v;
            }
            $periodBounds = DateTimeUtils::periodBounds($period->getPeriod());

            if( !array_key_exists($key, $group) ){
                $daysDetails = $this->getDaysPeriodInfosPerson($period->getDeclarer(), $period->getYear(), $period->getMonth());
                $periodLength = 0.0;

                foreach ($daysDetails as $d) {
                    $periodLength += $d['dayLength'];
                }
                $group[$key] = [
                    'period' => $period->getPeriod(),
                    'validators' => $validators,
                    'totalDays' => $periodBounds['totalDays'],
                    'periodLength' => $periodLength,
                    'periodFirstDay' => $periodBounds['firstDay'],
                    'periodLastDay' => $periodBounds['lastDay'],
                    'validableStep' => false,
                    'person' => (string)$period->getDeclarer(),
                    'person_id' => $period->getDeclarer()->getId(),
                    'declarations_activities' => [],
                    'declarations_others' => [],
                    'declarations_off' => [
                        'timesheets' => [],
                        'total' => 0.0,
                        'validators' => []
                    ],
                    'details' => $daysDetails,
                ];
            }

            if( $validateCurrentState == true ){
                $group[$key]['validableStep'] = true;
            }

            // Modèle commun
            $periodDatas = [
                'validationperiod_id' => $period->getId(),
                'validationperiod_object' => $period->getObject(),
                'validationperiod_objectid' => $period->getObjectId(),
                'validableStep' => $validateCurrentState,
                'validable' => $period->isValidable(),
                'validators' => $validators,
                'currentStep' => 5,
                'total' => 0.0,
                'label' => "Inconnu",
                'status' => $period->getStatus(),
                'statusMessage' => $this->getStatusMessage($period->getStatus()),
            ];


            // Déclarations sur des activités
            if( $period->getObject() == 'activity' ){
                /** @var Activity $activity */
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($period->getObjectId());
                $activityDatas = [
                    'label' => $activity->getFullLabel(),
                    'workpackages' => []
                ];

                /** @var WorkPackage $workpackage */
                foreach ($activity->getWorkPackages() as $workpackage) {
                    $inWorkpackage = $workpackage->hasPerson($period->getDeclarer());
                    $activityDatas['workpackages'][$workpackage->getCode()] = [
                        'code' => $workpackage->getCode(),
                        'enabled' => $inWorkpackage,
                        'label' => $workpackage->getLabel(),
                        'total' => 0.0,
                        'timesheets' => []
                    ];
                }

                $periodDatas = array_merge($periodDatas, $activityDatas);

                $group[$key]['declarations_activities'][$activity->getId()] = $periodDatas;
            }
            // Déclarations Hors-Lot
            else {
                $otherInfos = $this->getOthersWPByCode($period->getObject());
                $periodDatas['label'] = $otherInfos['label'];
                $periodDatas['timesheets'] = [];
                $group[$key]['declarations_others'][$otherInfos['code']] = $periodDatas;
            }
        }

        foreach ($group as $periodPerson=>$periodPersonDatas ){
            $period = $periodPersonDatas['period'];
            $personId = $periodPersonDatas['person_id'];
            $declarer = $this->getEntityManager()->getRepository(Person::class)->find($personId);

            $timesheets = $this->getTimesheetsPersonPeriod($declarer, $periodPersonDatas['periodFirstDay'], $periodPersonDatas['periodLastDay']);

            /** @var TimeSheet $timesheet */
            foreach ($timesheets as $timesheet) {
                $dayStr = $timesheet->getDateFrom()->format('j');
                $activity = $timesheet->getActivity();
                if( $activity ){
                    $main = $activity->getId();
                    $sub = $timesheet->getWorkpackage()->getCode();

                    if( array_key_exists($main, $periodPersonDatas['declarations_activities']) ){
                        $periodPersonDatas['declarations_activities'][$main]['workpackages'][$sub]['timesheets'][$dayStr] += $timesheet->getDuration();
                        $periodPersonDatas['declarations_activities'][$main]['workpackages'][$sub]['total'] += $timesheet->getDuration();
                        $periodPersonDatas['declarations_activities'][$main]['total'] += $timesheet->getDuration();
                    } else {
                        $periodPersonDatas['declarations_off']['validators'] = $validators;
                        $periodPersonDatas['declarations_off']['total'] += $timesheet->getDuration();
                        $periodPersonDatas['declarations_off']['timesheets'][$dayStr] += $timesheet->getDuration();
                    }

                } else {
                    $main = 'others';
                    $sub = $timesheet->getLabel();

                    if( array_key_exists($sub, $periodPersonDatas['declarations_others']) ){
                        $periodPersonDatas['declarations_others'][$sub]['timesheets'][$dayStr] += $timesheet->getDuration();
                        $periodPersonDatas['declarations_others'][$sub]['total'] += $timesheet->getDuration();
                    } else {
                        $periodPersonDatas['declarations_off']['total'] += $timesheet->getDuration();
                        $periodPersonDatas['declarations_off']['timesheets'][$dayStr] += $timesheet->getDuration();
                        $periodPersonDatas['declarations_off']['validators'] = $validators;
                    }
                }
                $periodPersonDatas['total'] += $timesheet->getDuration();
                $periodPersonDatas['details'][$dayStr]['duration'] += $timesheet->getDuration();
            }
                $output[] = $periodPersonDatas;
        }

        return $output;
    }

    public function getValidationsForValidator(Person $person)
    {

        $timesheetFormatter = new TimesheetsMonthFormatter();

        // Configuration des Hors-Lots
        $hwp = $this->getOthersWP();

        // Récupérations des périodes à valider
        $periods = $this->getValidationToDoPerson($person);

        $out = [];

        /** @var ValidationPeriod $period */
        foreach ($periods as $period) {
            $timesheets = $this->getTimesheetsValidationPeriod($period);


            $code = array_key_exists($period->getObject(), $hwp) ? $period->getObject() : 'other';
            $wpDetails = false;
            $details = null;
            $wps = null;

            if ($period->getObject() == 'activity') {
                /** @var Activity $activity */
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($period->getObjectId());
                $label = sprintf('[%s] %s', $activity->getAcronym(), $activity->getLabel());
                $description = $activity->getDescription();
                $code = "activity";
                $wpDetails = true;
                $wps = [];
                foreach ($activity->getWorkPackages() as $wp) {
                    $wps[$wp->getCode()] = 0.0;
                }
            } else {
                $label = array_key_exists($period->getObject(), $hwp) ? $hwp[$period->getObject()]['label'] : 'Non-définit';
                $code = array_key_exists($period->getObject(), $hwp) ? $hwp[$period->getObject()]['code'] : 'n-d';
                $description = $hwp[$code]['description'];
            }

            $periodDatas = $timesheetFormatter->format($timesheets, $period->getMonth(), $period->getYear(), $wps);
            $periodDatas['period_id'] = $period->getId();
            $periodDatas['person'] = (string)$period->getDeclarer();
            $periodDatas['type'] = $period->getObject();
            $periodDatas['label'] = $label;
            $periodDatas['description'] = $description;
            $periodDatas['code'] = $code;
            $periodDatas['validation'] = $period->json();
            $periodDatas['details'] = $details;

            $out['packages'][] = $periodDatas;
        }

        return $out;
    }

    public function getDatasOutOfWorkPackageToValidate(Person $person)
    {

        $timesheetFormatter = new TimesheetsMonthFormatter();
        $hwp = $this->getOthersWP();
        $periods = $this->getValidationPeriodsOutWP($person);
        $out = [
            'label' => 'Déclaration hors-lot pour ' . (string)$person,
            'packages' => []
        ];

        foreach ($periods as $period) {
            $timesheets = $this->getTimesheetsValidationPeriod($period);
            $code = array_key_exists($period->getObject(), $hwp) ? $period->getObject() : 'other';

            $periodDatas = $timesheetFormatter->format($timesheets, $period->getMonth(), $period->getYear());
            $periodDatas['label'] = $hwp[$code]['label'];
            $periodDatas['description'] = $hwp[$code]['description'];
            $periodDatas['code'] = $code;
            $periodDatas['validation'] = $period->json();
            $periodDatas['period_id'] = $period->getId();
            $out['packages'][] = $periodDatas;
        }

        return $out;
    }

    /**
     * Retourne l'ensemble des créneaux utils à la validation d'une période.
     *
     * @param ValidationPeriod $validationPeriod
     * @return array
     */
    public function getTimesheetsForValidationPeriod(ValidationPeriod $validationPeriod)
    {
        $year = $validationPeriod->getYear();
        $month = $validationPeriod->getMonth();
        $dateRef = new \DateTime(sprintf('%s-%s-01', $year, $month));

        // Nombre de jours dans le mois
        $nbr = cal_days_in_month(CAL_GREGORIAN, (int)$dateRef->format('m'), (int)$dateRef->format(('Y')));


        // TODO marquer les jours feriès

        $person = $this->getEntityManager()->getRepository(Person::class)->find($validationPeriod->getDeclarer()->getId());


        // Autres périodes
        $query = $this->getEntityManager()->getRepository(ValidationPeriod::class)
            ->createQueryBuilder('v')
            ->where('v.month = :month AND v.year = :year AND v.declarer = :person')
            ->setParameters([
                'month' => $month,
                'year' => $year,
                'person' => $person,
            ]);

        $daysDetails = $this->getDaysPeriodInfosPerson($person, $validationPeriod->getYear(), $validationPeriod->getMonth());

        $daysLength = [];
        $daysClosed = [];
        $daysInfos = [];
        $daysLabels = [];

        foreach ($daysDetails as $date => $data) {
            $daysLength[$date] = $data['duration'];
            $daysClosed[$date] = $data['close'];
            $daysInfos[$date] = $data['infos'];
            $daysLabels[$date] = $data['label'];
        }

        $periodsAtSameMoment = $query->getQuery()->getResult();
        $output = [
            'main' => '',
            'displayHours' => $this->isDeclarationsHoursPerson($person),
            'dayLength' => $this->getDayDuration($person),
            'monthLength' => $this->getMonthDuration($person, $year, $month),
            'projects' => [],
            'others' => [],
            'total' => [],
            'daysLabels' => $daysLabels,
            'daysLabels' => $daysLabels,
            'daysClosed' => $daysClosed,
            'daysLength' => $daysLength,
            'daysInfos' => $daysInfos,
            'declarant' => (string)$person,
            'nbrDays' => $nbr
        ];

        $total = [];

        /** @var ValidationPeriod $vp */
        foreach ($periodsAtSameMoment as $vp) {

            $timesheetsPeriod = $this->getTimesheetsValidationPeriod($vp);


            if ($vp->getId() == $validationPeriod->getId()) {
                $output['main'] = $this->getArrayFormatedTimesheetsFull($vp, $timesheetsPeriod, $total);
            } else {
                if ($vp->getObjectGroup() == ValidationPeriod::GROUP_WORKPACKAGE) {
                    $output['projects'][] = $this->getArrayFormatedTimesheetsCompact($vp, $timesheetsPeriod, $total);
                } else {
                    $output['others'][] = $this->getArrayFormatedTimesheetsCompact($vp, $timesheetsPeriod, $total);
                }
            }
        }

        $output['total'] = $total;
        $fullTotal = 0.0;

        foreach ($total as $day => $duration) {
            $fullTotal += $duration;
        }

        $output['totalFull'] = $fullTotal;


        return $output;
    }

    /**
     * @param Person $person
     * @param $year
     * @param $month
     * @param $code
     * @return null|ValidationPeriod
     * @throws OscarException
     */
    public function getValidationPeriosOutOfWorkpackageAt(Person $person, $year, $month, $code)
    {
        return $this->getValidationPeriodRepository()->getValidationPeriodOutWP($year, $month, $code, $person->getId());
    }

    /**
     * @param TimeSheet[] $timesheets
     */
    public function getArrayFormatedTimesheetsCompact(ValidationPeriod $validationPeriod, $timesheets, &$total)
    {
        $output = [];
        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {
            if ($timesheet->getActivity()) {
                $label = $pack = $timesheet->getActivity()->getLabel();
                $code = $timesheet->getActivity()->getAcronym();
                $objId = $timesheet->getId();
            } else {
                $pack = $code = $timesheet->getLabel();

                $label = $this->getOthersWPByCode($code)['label'];
                $objId = 0;
            }
            if (!array_key_exists($pack, $output)) {
                $output[$pack] = [
                    'oid' => $objId,
                    'validationperiod_id' => $validationPeriod->getId(),
                    'validationperiod_state' => $validationPeriod->getState(),
                    'validation_icon' => 'icon-' . $validationPeriod->getStatus(),
                    'label' => $label,
                    'code' => $code,
                    'days' => [],
                    'total' => 0.0
                ];
            }

            $day = $timesheet->getDateFrom()->format('d');

            if (!array_key_exists($day, $output[$pack]['days'])) {
                $output[$pack]['days'][$day] = 0.0;
            }

            $output[$pack]['days'][$day] += $timesheet->getDuration();
            $output[$pack]['total'] += $timesheet->getDuration();
            $total[$day] += $timesheet->getDuration();
        }

        if (count($output) == 1) {
            return $output[$pack];
        }

        return $output;
    }

    /**
     * Retourne TRUE si la personne déclare en heure, sinon retourne FALSE.
     *
     * @param Person $person
     * @return bool
     * @throws OscarException
     */
    public function isDeclarationsHoursPerson(Person $person)
    {

        $declarationShours = $default = $this->getOscarConfig()->getConfiguration('declarationsHours');

        if ($this->getOscarConfig()->getConfiguration('declarationsHoursOverwriteByAuth')) {
            try {
                if (!$person || !$person->getLadapLogin()) {
                    throw new \Exception("Cette personne n'existe pas ou n'a pas de login valide.");
                }

                /** @var Authentification $auth */
                $auth = $this->getEntityManager()->getRepository(Authentification::class)->findOneBy(['username' => $person->getLadapLogin()]);
                if (!$auth) {
                    throw new \Exception("La personne '$person' n'a pas de compte actif sur Oscar.");
                }

                $declarationShours = $auth->getSetting('declarationsHours', $default);


            } catch (\Exception $e) {
                $this->getLogger()->err("ERROR : " . $e->getMessage());
            }
        }

        return $declarationShours;
    }


    /**
     * Retourne la durée standard d'une journée pour la personne.
     *
     * @param Person $person
     * @return mixed
     * @throws OscarException
     */
    public function getDayDuration(Person $person, $day = null)
    {
        $config = $this->getDayLengthPerson($person);
        $day = (string)$day;
        if (array_key_exists($day, $config['days'])) {
            return $config['days'][$day];
        } else {
            return $config['value'];
        }
    }

    public function getDayMaxLengthPerson(Person $person, $day = null)
    {
        $config = $this->getDayLengthPerson($person);
        return $config['max'];
    }

    public function getDayMinLengthPerson(Person $person, $day = null)
    {
        $config = $this->getDayLengthPerson($person);
        return $config['min'];
    }


    public function getDayLengthPerson(Person $person)
    {
        $configApp = $this->getOscarConfig()->getConfiguration('declarationsDurations.dayLength');
        if ($person->getCustomSettingsKey('days')) {
            $customDays = $person->getCustomSettingsKey('days');
            foreach ($customDays as $day => $value) {
                $configApp['days'][$day] = $value;
            }
            if ($person->getCustomSettingsKey('days_request')) {
                $configApp['days_request'] = $person->getCustomSettingsKey('days_request');
            }
        }
        return $configApp;
    }

    public function getMonthDuration(Person $person, $year, $month)
    {
        $daysInfos = $this->getDaysPeriodInfosPerson($person, $year, $month);
        $total = 0.0;
        foreach ($daysInfos as $dayInfo) {
            $total += $dayInfo['duration'];
        }
        return $total;
    }

    public function getActivitiesWithDeclarant( Person $person ){
        return $this->getEntityManager()->getRepository(Activity::class)->createQueryBuilder('a')
            ->innerJoin('a.workPackages', 'wp')
            ->innerJoin('wp.persons', 'wpp')
            ->where('wpp.person = :person')
            ->getQuery()
            ->setParameter('person', $person)
            ->getResult();

    }

    private $_cache_getPeriodDuration = [];

    public function getPeriodDuration( $person, $year, $month){
        $key = sprintf('%s_%s-%s', $person->getId(), $year, $month);
        if( !array_key_exists($key, $this->_cache_getPeriodDuration) ){
            $periodInfos = $this->getDaysPeriodInfosPerson($person, $year, $month);
            $totalPeriod = 0.0;
            foreach ($periodInfos as $key=>$day) {
                $totalPeriod += $day['dayLength'];
            }
            $this->_cache_getPeriodDuration = $totalPeriod;
        }
        return $this->_cache_getPeriodDuration = $totalPeriod;
    }

    public function getResumeActivity( Activity $activity ){

        $workPackages   = [];
        $periods        = [];
        $persons        = [];
        $total          = 0.0;

        /** @var WorkPackage $workPackage */
        foreach ($activity->getWorkPackages() as $workPackage) {
            $workPackages[$workPackage->getId()] = [
                'id'    => $workPackage->getId(),
                'code'  => $workPackage->getCode(),
                'label' => $workPackage->getLabel(),
                'total' => 0.0
            ];
        }

        $declarersIds = [];

        $validations = $this->getEntityManager()->getRepository(ValidationPeriod::class)->createQueryBuilder('vp')
            ->where('vp.object_id = :activityId AND vp.object = :object')
            ->setParameters([
                'activityId' => $activity->getId(),
                'object' => ValidationPeriod::OBJECT_ACTIVITY
            ])
            ->getQuery()
            ->getResult();

        $validationsState = [];
        /** @var ValidationPeriod $validation */
        foreach ($validations as $validation) {
            $key = $validation->getDeclarer()->getId() . '-' . $validation->getPeriod();
            $validationsState[$key] = $validation->getStatus();
        }


        /** @var Person $person */
        foreach ($activity->getDeclarers() as $person) {
            $declarersIds[] = $person->getId();
            $persons[$person->getId()] = [
                'id'            => $person->getId(),
                'displayname'   => (string)$person,
                'total'         => 0.0
            ];
        }

        // Début / Fin
        $periodsList = DateTimeUtils::allperiodsBetweenTwo($activity->getDateStart()->format('Y-m'), $activity->getDateEnd()->format('Y-m'));
        foreach ($periodsList as $period) {

            $periodBounds = DateTimeUtils::periodBounds($period);

            $periods[$period] = [
                'total' => 0.0,
                'days' => $periodBounds['days'],
                'workpackages' => [],
                'persons' => []
            ];
            foreach ($persons as $person) {

                $personId = $person['id'];
                $validationKey = sprintf('%s-%s', $personId, $period);
                $periods[$period]['persons'][$personId] = [
                    'total' => 0.0,
                    'validation_state' => array_key_exists($validationKey, $validationsState) ? $validationsState[$validationKey] : 'none',
                    'displayname' => $person['displayname'],
                    'workpackages' => [],
                    'details' => [],
                ];
                foreach ($workPackages as $wp) {
                    $periods[$period]['persons'][$personId]['workpackages'][$wp['id']] = 0.0;
                }
            }
        }

        $timessheets = $this->getEntityManager()->getRepository(TimeSheet::class)->createQueryBuilder('t')
            ->innerJoin('t.person', 'd')
            ->where('d.id IN (:declarers) AND t.activity = :activity')
            ->setParameters([
                'declarers' => $declarersIds,
                'activity' => $activity
            ])
            ->getQuery()
            ->getResult();

        /** @var TimeSheet $timesheet */
        foreach ($timessheets as $timesheet) {
            $personId   = $timesheet->getPerson()->getId();
            $period     = $timesheet->getDateFrom()->format('Y-m');
            $wpCode     = $timesheet->getWorkpackage()->getCode();
            $wpId       = $timesheet->getWorkpackage()->getId();
            $duration   = $timesheet->getDuration();
            $day        = $timesheet->getDateFrom()->format('j');

            $total += $duration;
            $workPackages[$wpId]['total'] += $duration;
            $persons[$personId]['total'] += $duration;
            $periods[$period]['total'] += $duration;
            $periods[$period]['persons'][$personId]['total'] += $duration;
            $periods[$period]['persons'][$personId]['workpackages'][$wpId] += $duration;

            if( !array_key_exists($wpId, $periods[$period]['persons'][$personId]['details']) ){
                $periods[$period]['persons'][$personId]['details'][$wpId] = [];
            }

            if( !array_key_exists($day, $periods[$period]['persons'][$personId]['details'][$wpId]) ){
                $periods[$period]['persons'][$personId]['details'][$wpId][$day] = 0.0;
            }

            $periods[$period]['persons'][$personId]['details'][$wpId][$day] += $duration;
        }

        $datas = [
            'test'          => count($timessheets),
            'total'         => $total,
            'activity_id'   => $activity->getId(),
            'acronym'       => $activity->getAcronym(),
            'label'         => $activity->getLabel(),
            'workspackages' => $workPackages,
            'persons'       => $persons,
            'periods'       => $periods,
        ];

        return $datas;
    }

    /**
     * Retourne le résumé complet des déclarations d'une personne.
     *
     * @param Person $person
     * @return array
     */
    public function getResumePerson( Person $person ){
        $declarantInActivities = $this->getActivitiesWithDeclarant($person);

        $now = new \DateTime();
        $periodNow = $now->format('Y-m');
        $minPeriod = $now->getTimestamp();
        $maxPeriod = $now->getTimestamp();

        $datas = [
            'owner' => $person == $this->getOscarUserContext()->getCurrentPerson(),
            'minDate' => "",
            'maxDate' => "",
            'periods' => [],
            'activities' => [],
            'validations' => [],
            'horslots' => []
        ];

        $periodsDetails = [];

        foreach ($this->getOthersWP() as $hl) {
            $datas['horslots'][$hl['code']] = [
                'code' => $hl['code'],
                'label' => $hl['label']
            ];
        }

        /** @var Activity $activity */
        foreach ($declarantInActivities as $activity) {

            $activityDatas = [
                'id' => $activity->getId(),
                'acronym' => $activity->getAcronym(),
                'label' => $activity->getLabel(),
                'workpackages' => [],
                'total' => 0.0,
                'start' => $activity->getDateStartStr(),
                'dateend' => $activity->getDateEndStr(),
            ];

            $workPackagesId = [];

            /** @var WorkPackage $workPackage */
            foreach ($activity->getWorkPackages() as $workPackage) {
                $workPackagesId[] = $workPackage->getId();
                $activityDatas['workpackages'][$workPackage->getId()] = [
                    'id' => $workPackage->getId(),
                    'code' => $workPackage->getCode(),
                    'label' => $workPackage->getLabel(),
                    'available' => $workPackage->hasPerson($person),
                    'total' => 0.0
                ];
            }

            if( $activity->getDateStart() && $activity->getDateEnd() ){
                $periods = DateTimeUtils::allperiodsBetweenTwo($activity->getDateStart()->format('Y-m'), $activity->getDateEnd()->format('Y-m'));
                foreach ($periods as $period) {
                    if( !array_key_exists($period, $periodsDetails) ){
                        $split = explode('-', $period);
                        $year = $split[0];
                        $month = $split[1];
                        $periodsDetails[$period] = [
                            'period'            => $period,
                            'person_id'         => $person->getId(),
                            'person'            => (string)$person,
                            'month'             => $month,
                            'year'              => $year,
                            'periodDuration'    => $this->getPeriodDuration($person, $year, $month),
                            //'periodValidation'  => $this->getPeriodValidation
                            'past'              => $period < $periodNow,
                            'current'           => $period == $periodNow,
                            'futur'             => $period > $periodNow,
                            'activities_id'     => [],
                            'workpackages_id'   => [],
                            'unexpected'        => false,
                            'total'             => 0.0,
                            'total_activities'  => 0.0,
                            'total_activities_details' => [],
                            'total_horslots'    => 0.0,
                            'validation_state'  => 'none',
                            'validations_id'    => []
                        ];
                    }
                    $periodsDetails[$period]['activities_id'][] = $activity->getId();
                    $periodsDetails[$period]['total_activities_details'][$activity->getId()] = 0.0;
                    $periodsDetails[$period]['workpackages_id'] = $workPackagesId;
                }
            }

            if( $activity->getDateStart() ) {
                $minPeriod = min($minPeriod, $activity->getDateStart()->getTimestamp());
            }
            if( $activity->getDateEnd() ) {
                $maxPeriod = max($maxPeriod, $activity->getDateEnd()->getTimestamp());
            }

            $datas['activities'][$activityDatas['id']] = $activityDatas;
        }

        $timesheets = $this->getEntityManager()->getRepository(TimeSheet::class)->findBy(['person' => $person]);

        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {
            $period = $timesheet->getPeriodCode();
            if( !array_key_exists($period, $periodsDetails) ){
                $year = $timesheet->getDateFrom()->format('Y');
                $month = $timesheet->getDateFrom()->format('m');
                $periodsDetails[$period] = [
                    'period' => $period,
                    'month'             => $month,
                    'year'              => $year,
                    'periodDuration'    => $this->getPeriodDuration($person, $year, $month),
                    'activities_id'     => [],
                    'workpackages_id'   => [],
                    'unexpected'        => true,
                    'total'             => 0.0,
                    'total_activities'  => 0.0,
                    'total_horslots'    => 0.0,
                    'validations_id'    => []
                ];
            }
            $total = $timesheet->getDuration();

            $periodsDetails[$period]['total'] += $total;
            if( $timesheet->getActivity() ){
                $periodsDetails[$period]['total_activities'] += $total;
                $periodsDetails[$period]['total_activities_details'][$timesheet->getActivity()->getId()] += $total;
            } else {
                $periodsDetails[$period]['total_horslots'] += $total;

            }
        }

        $validations_states = [
            ValidationPeriod::STATUS_CONFLICT,
            ValidationPeriod::STATUS_STEP1,
            ValidationPeriod::STATUS_STEP2,
            ValidationPeriod::STATUS_STEP3,
            ValidationPeriod::STATUS_VALID,
            'none',
            ];

        $declarations = $this->getEntityManager()->getRepository(ValidationPeriod::class)->findBy(['declarer' => $person]);
        /** @var ValidationPeriod $declaration */
        foreach ($declarations as $declaration) {
            $period = DateTimeUtils::getCodePeriod($declaration->getYear(), $declaration->getMonth());
            $currentStatusIndex = array_search($periodsDetails[$period]['validation_state'], $validations_states);
            $declarationStatusIndex = array_search($declaration->getStatus(), $validations_states);

            if( $declarationStatusIndex < $currentStatusIndex ){
                $periodsDetails[$period]['validation_state'] = $declaration->getStatus();
                $periodsDetails[$period]['validators'] = [];
                if( $declaration->requireValidation() ){
                    foreach ( $declaration->getCurrentValidators() as $validator ){
                        $periodsDetails[$period]['validators'][] = (string)$validator;
                    }
                }
            }
            else if( $declarationStatusIndex == $currentStatusIndex ){
                if( $declaration->requireValidation() ) {
                    foreach ( $declaration->getCurrentValidators() as $validator ){
                        $validatorStr = (string)$validator;

                        if (!in_array($validatorStr, $periodsDetails[$period]['validators'])) {
                            $periodsDetails[$period]['validators'][] = (string)$validator;
                        }
                    }
                }
            }

            $periodsDetails[$period]['validations_id'][] = $declaration->getId();
            $datas['validations'][$declaration->getId()] = $declaration->toJson();
        }

        $datas['periods'] = $periodsDetails;
        $datas['minDate'] = \DateTime::createFromFormat('U', $minPeriod)->format('Y-m');
        $datas['maxDate'] = \DateTime::createFromFormat('U', $maxPeriod)->format('Y-m');

        return $datas;
    }

    public function getDaysPeriodInfosPerson(Person $person, $year, $month)
    {

        $dateRef = new \DateTime(sprintf('%s-%s-01', $year, $month));
        $lockedDatas = $this->getLockedDays($year, $month);

        // Nombre de jours dans le mois
        $nbr = cal_days_in_month(CAL_GREGORIAN, (int)$dateRef->format('m'), (int)$dateRef->format(('Y')));

        $daysFull = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
        $decaleDay = $dateRef->format('N') - 1;
        $daysLabels = [];

        $weekendAllowed = $this->getOscarConfig()->getConfiguration('declarationsWeekend') == false;

        $amplitudeMax = $this->getOscarConfig()->getConfiguration('declarationAmplitudeMax');
        $amplitudeMin = $this->getOscarConfig()->getConfiguration('declarationAmplitudeMin');

        $daysDetails = $this->getDayLengthPerson($person);

        $declarations = $this->getEntityManager()->getRepository(ValidationPeriod::class)
            ->createQueryBuilder('d')
            ->where('d.year = :year AND d.month = :month AND d.declarer = :person')
            ->getQuery()
            ->setParameters([
                'year' => $year,
                'month' => $month,
                'person' => $person,
            ])
            ->getResult();

        if( count($declarations) ){
            $daysDetails = json_decode($declarations[0]->getSchedule(), JSON_OBJECT_AS_ARRAY);
        }

        for ($i = $decaleDay; $i < $nbr + $decaleDay; $i++) {
            $day = $i - $decaleDay + 1;
            $dayIndex = ($i % 7);
            $dayOfWeek = $dayIndex +1;

            $duration = $daysDetails['days'][$dayOfWeek];
            $maxlength = $daysDetails['max'];
            $minlength = $daysDetails['min'];

            $amplitudemax = $duration * $amplitudeMax;
            $amplidudemin = $duration * $amplitudeMin;


            $close = false;
            $infos = "";
            $dayKey = $day; // < 10 ? '0' . $day : "" . $day;
            $lockedKey = "$year-".intval($month)."-$day";

            // Jour fermé (provisoire)
            $locked = false;
            $lockedReason = "";

            // Jour fermé (définitif)
            $closed = false;
            $closedReason = "";

            if ($dayIndex > 4 && $weekendAllowed == true) {
                $duration = 0.0;
                $maxlength = 0.0;
                $minlength = 0.0;
                $closed = true;
                $locked = true;
                $closedReason = $lockedReason = $infos = "Weekend";
            }

            if (array_key_exists($lockedKey, $lockedDatas)) {
                $duration = 0.0;
                $maxlength = 0.0;
                $minlength = 0.0;
                $closed = true;
                $locked = true;
                $closedReason = $lockedReason = $infos = "Fermé " . $lockedDatas[$lockedKey];
            }

            if (array_key_exists($dayIndex + 1, $daysDetails)) {
                $duration = $daysDetails[$dayIndex + 1];
            }

//            $daysLabels[$dayKey] =  $daysFull[$dayIndex];
            $days[$dayKey] = [
                'duration' => 0.0,
                'dayLength' => $duration,
                'maxLength' => $maxlength,
                'minLength' => $minlength,
                'amplitudemin' => $amplidudemin,
                'amplitudemax' => $amplitudemax,
                'label' => $daysFull[$dayIndex],
                'close' => $close,
                'locked' => $locked,
                'lockedReason' => $lockedReason,
                'closed' => $closed,
                'closedReason' => $closedReason,
                'infos' => $infos,
                'datefull' => $lockedKey,
            ];
        }
        ksort($days, SORT_NATURAL);
        return $days;
    }

    public function verificationPeriod(Person $person, $year, $month)
    {
        $datas = $this->getTimesheetDatasPersonPeriod($person, sprintf('%s-%s', $year, $month));

        $warnings = [];
        $errors = [];

        $weeksMaxCount = [];
        $weeksMinCount = [];

        $month = 0.0;
        $limitMinMonth = 0.0;


        foreach ($datas['days'] as $day => $dayData) {
            $week = $dayData['week'];

            $duration = $dayData['total'];
            $min = $dayData['minLength'];
            $max = $dayData['maxLength'];

            if ($duration > $max) {
                $errors[] = "- Les heures déclarées le jour " . $dayData['i'] . " dépassent la durée autorisée !";
            }

            if ($min > 0.0 && $duration == 0.0) {
                $errors[] = "- Il doit y avoir des heures de déclarées le jour " . $dayData['i'] . " (" . $min . ") !";
            }


            if (!array_key_exists($week, $weeksMaxCount))
                $weeksMaxCount[$week] = 0.0;

            if (!array_key_exists($week, $weeksMinCount))
                $weeksMinCount[$week] = 0.0;


            $weeksMaxCount[$week] += $duration;
            $weeksMinCount[$week] += $min;
            $limitMinMonth += $min;
            $month += $duration;
        }

        $limitWeekMax = $this->getOscarConfig()->getConfiguration('declarationsDurations.weekLength.max');
        $limitMonthMax = $this->getOscarConfig()->getConfiguration('declarationsDurations.monthLength.max');

        // @todo Faire comme pour la semaine
        $limitMonthMin = $this->getOscarConfig()->getConfiguration('declarationsDurations.monthLength.min');

        foreach ($weeksMaxCount as $week => $weekDuration) {
            if ($weekDuration > $limitWeekMax) {
                $errors[] = sprintf("- Les heures déclarées en semaine %s dépassent la durée autorisée", $week);
            }
        }

        /*
        foreach ($weeksMinCount as $week=>$weekDuration) {
            if( $weekDuration > $limitWeekMax ){
                $errors[] = sprintf("- Les heures déclarées en semaine %s dépassent la durée autorisée", $week);
            }
        }*/

        if ($month > $limitMonthMax) {
            $errors[] = "- Les heures déclarées pour ce mois dépassent la durée autorisée";
        }

        if ($month < $limitMonthMin) {
            $errors[] = "- Les heures déclarées pour ce mois sont de deça la durée attendue";
        }

        if (count($errors) > 0) {
            throw new OscarException(sprintf("Il y'a %s erreur(s) dans votre déclaration : \n %s", count($errors), implode("\n", $errors)));
        }


        return true;
    }

    /**
     * @param TimeSheet[] $timesheets
     */
    public function getArrayFormatedTimesheetsFull(ValidationPeriod $validationPeriod, $timesheets, &$total)
    {
        $output = [];
        $totalByDays = [];
        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {
            if ($timesheet->getActivity()) {
                $packId = $timesheet->getActivity()->getId();
                $pack = $timesheet->getActivity()->getLabel();
                $num = $timesheet->getActivity()->getOscarNum();
                $acronym = $timesheet->getActivity()->getAcronym();
                $subpack = $timesheet->getWorkpackage()->getCode();
                $subpackId = $timesheet->getWorkpackage()->getId();
            } else {
                $packId = 0;
                $pack = 'Autres';
                $acronym = 'other';
                $num = null;
                $subpack = $timesheet->getLabel();
                $subpackId = 0;
            }

            if (!array_key_exists($pack, $output)) {
                $output[$pack] = [
                    'oid' => $packId,
                    'validationperiod_id' => $validationPeriod->getId(),
                    'validationperiod_status' => $validationPeriod->getStatus(),
                    'validationperiod' => $validationPeriod->getState(),
                    'acronym' => $acronym,
                    'label' => $pack,
                    'OscarId' => $num,
                    'totalPeriod' => 0.0,
                    'totalDays' => [],
                    'details' => [

                    ]
                ];
            }

            if (!array_key_exists($subpack, $output[$pack]['details'])) {
                $output[$pack]['details'][$subpack] = [
                    'oid' => $subpackId,
                    'label' => $subpack,
                    'days' => [],
                    'total' => 0.0
                ];
            }

            $day = $timesheet->getDateFrom()->format('d');
            if (!array_key_exists($day, $output[$pack]['details'][$subpack]['days'])) {
                $output[$pack]['details'][$subpack]['days'][$day] = 0.0;
            }

            if (!array_key_exists($day, $output[$pack]['totalDays'])) {
                $output[$pack]['totalDays'][$day] = 0.0;
            }

            $output[$pack]['details'][$subpack]['days'][$day] += $timesheet->getDuration();
            $output[$pack]['details'][$subpack]['total'] += $timesheet->getDuration();
            $output[$pack]['totalPeriod'] += $timesheet->getDuration();
            $output[$pack]['totalDays'][$day] += $timesheet->getDuration();

            $total[$day] += $timesheet->getDuration();
        }


        return $output;
    }

    public function getTimesheetsValidationPeriod(ValidationPeriod $validationPeriod)
    {

        // Récupération des dates de la périodes
        $year = $validationPeriod->getYear();
        $month = $validationPeriod->getMonth();
        $dateRef = new \DateTime(sprintf('%s-%s-01', $year, $month));

        // Nombre de jours dans le mois
        $nbr = cal_days_in_month(CAL_GREGORIAN, (int)$dateRef->format('m'), (int)$dateRef->format(('Y')));
        $from = $dateRef->format('Y-m-01 00:00:00');
        $to = $dateRef->format('Y-m-' . $nbr . ' 23:59:59');

        $query = $this->getEntityManager()->getRepository(TimeSheet::class)
            ->createQueryBuilder('t')
            ->where('t.person = :person AND t.dateFrom >= :from AND t.dateTo <= :to');

        $parameters = [
            'person' => $validationPeriod->getDeclarer(),
            'from' => $from,
            'to' => $to,
        ];

        if ($validationPeriod->getObjectGroup() == ValidationPeriod::GROUP_OTHER) {
            $query->andWhere('t.label = :label');
            $parameters['label'] = $validationPeriod->getObject();
        }

        if ($validationPeriod->getObjectGroup() == ValidationPeriod::GROUP_WORKPACKAGE) {
            $query->andWhere('t.activity = :activity');
            $parameters['activity'] = $validationPeriod->getObjectId();
        }

        $query->orderBy('t.dateFrom');

        $query->setParameters($parameters);
        return $query->getQuery()->getResult();
    }

    public function deleteValidationPeriodPerson(Person $person, $period)
    {
        $spli = explode('-', $period);
        $year = (int)$spli[0];
        $month = (int)$spli[1];

        $query = $this->getEntityManager()->createQueryBuilder('vp')
            ->delete(ValidationPeriod::class, 'vp')
            ->where('vp.year = :year AND vp.month = :month AND vp.declarer = :person')
            ->setParameters([
                'year' => $year,
                'month' => $month,
                'person' => $person,
            ])
            ->getQuery();

        $query->execute();
        return true;

    }

    /**
     * Récupération des validations actives pour la période donnée.
     *
     * @param Person $person
     * @param $month
     * @param $year
     * @return array
     */
    public function getPeriodValidation(Person $person, $month, $year)
    {
        $query = $this->getEntityManager()->getRepository(ValidationPeriod::class)
            ->createQueryBuilder('v')
            ->where('v.month = :month AND v.year = :year AND v.declarer = :personId')
            ->setParameters([
                'personId' => $person->getId(),
                'year' => $year,
                'month' => $month
            ]);

        // On organise les résultat avec des clefs correspondantes au type d'objet/ID
        $result = [];
        /** @var ValidationPeriod $validationPeriod */
        foreach ($query->getQuery()->getResult() as $validationPeriod) {
            if (array_key_exists($validationPeriod->getPeriodKey(), $result)) {
                $this->getLogger()->err(sprintf("L'objet ValidationPeriod %s a un doublon !", $validationPeriod));
            }
            $result[$validationPeriod->getPeriodKey()] = $validationPeriod;
        }
        return $result;
    }


    public function getPersonPeriods(Person $person, $period)
    {

        $periodDatas = DateTimeUtils::extractPeriodDatasFromString($period);
        $periodCode = $periodDatas['periodCode'];

        // Périodes
        $periodQuery = $this->getValidationPeriodRepository()->getValidationPeriodsPersonQuery($person->getId());
        $periodQuery->andWhere('vp.year = :year AND vp.month = :month')
            ->setParameters([
                'year' => $periodDatas['year'],
                'month' => $periodDatas['month'],
                'personId' => $person->getId()
            ])
            ->getQuery()->getResult();

        $out = [
            "$periodCode" => [
                'periodCode' => $periodCode,
                'hasValidation' => false,
                'days' => $this->getDaysPeriodInfosPerson($person, $periodDatas['year'], $periodDatas['month'])
            ]
        ];
        $periods = $periodQuery->getQuery()->getResult();

        /** @var ValidationPeriod $period */
        foreach ($periods as $period) {
            if ($period)

                $key = $period->getYear() . '-' . ($period->getMonth() < 10 ? '0' . $period->getMonth() : $period->getMonth());

            if (!array_key_exists($key, $out)) {

                $out[$key] = [
                    'periodCode' => $key,
                    'hasValidation' => true,
                    'days' => []
                ];
            }
        }

        $timesheets = $query = $this->getEntityManager()->getRepository(TimeSheet::class)
            ->createQueryBuilder('t')
            ->where('t.person = :person')
            ->setParameter('person', $person)
            ->getQuery()
            ->getResult();


        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {

            $month = (int)$timesheet->getDateFrom()->format('n');
            $year = (int)$timesheet->getDateFrom()->format('Y');

            if ($month != $periodDatas['month'] || $year != $periodDatas['year']) continue;


            $key = $timesheet->getDateFrom()->format('Y-m');
            $keyDay = $timesheet->getDateFrom()->format('d');

            $out[$key]['days'][$keyDay]['duration'] += $timesheet->getDuration();
        }

        return $out;
    }

    /**
     * Méthode de contrôle de l'éligibilité d'un créneau pour un lot donné.
     *
     * @param Person $person
     * @param \DateTime $start
     * @param \DateTime $to
     * @param WorkPackage $wp
     * @throws OscarException
     */
    public function checkAllowedAddedTimesheetInWorkPackage(Person $person, \DateTime $start, \DateTime $to, WorkPackage $wp)
    {
        $jourCreneau = $start->format('d/m/Y');

        // On test si la personne est bien identifiée comme déclarante sur le lot
        if (!$wp->hasPerson($person)) {
            throw new OscarException(sprintf("%s n'est pas identifié comme déclarant sur le lot %s", $person, $wp));
        }

        if ($wp->getDateStart() && $start < $wp->getDateStart()) {
            throw new OscarException(sprintf("Le lot %s n'est pas encore commencé au %s", $wp, $jourCreneau));
        }

        if ($wp->getDateEnd() && $start > $wp->getDateEnd()) {
            throw new OscarException(sprintf("Le lot %s est terminé au %s", $wp, $jourCreneau));
        }
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// CENTRALISER ICI !
    ///
    ///
    public function getTimesheetDatasPersonPeriod(Person $person, $period)
    {
        $output = [];

        $now = new \DateTime();

        // Récupération des données sur la période
        $periodFirstDay = new \DateTime($period . '-01');
        $year = (int)$periodFirstDay->format('Y');
        $month = (int)$periodFirstDay->format('m');
        $declarationInHours = $this->isDeclarationsHoursPerson($person);

        // periode suivante
        $todayMonth = (int)$now->format('m');
        $todayYear = (int)$now->format('Y');
        $todayDay = (int)$now->format('d');

        $currentMonthLength = cal_days_in_month(CAL_GREGORIAN, $todayMonth, $todayYear);

        if ($todayDay == $currentMonthLength) {
            if ($todayMonth == 12) {
                $todayMonth = 1;
                $todayYear += 1;
            }
        }
        $periodMax = sprintf('%s-%s', $todayYear, $todayMonth);

        $daysInfosPerson = $this->getDaysPeriodInfosPerson($person, $year, $month);
        $daysInfos = [];
        foreach ($daysInfosPerson as $dayNum => $data) {
            $daysInfos[(int)$dayNum] = $data;
        }

        $totalDays = count($daysInfos);
        $periodLastDay = new \DateTime($period . '-' . $totalDays . ' 23:59:59');

        $periodInfos = "";
        $periodFutur = $periodFirstDay > $now;
        $periodFinished = $periodLastDay < $now;
        $periodCurrent = $period == $now->format('Y-n');

        $submitable = false;
        $submitableInfos = "Vous ne pouvez pas soumettre cette période pour une raison inconnue";

        $editable = false;
        $editableInfos = "Vous ne pouvez pas modifier les déclarations pour cette période pour une raison inconnue";

        // Récupération des validations pour cette période
        $periodValidations = $this->getPeriodValidation($person, $month, $year);
        $isPeriodSend = count($periodValidations);
        $periodValidationsDt = [];

        $from = $periodFirstDay->format('Y-m-d');
        $to = $periodLastDay->format('Y-m-d');

        $periodLength = 0.0;
        $periodTotal = 0.0;
        $periodOpened = 0.0;
        $periodDeclarations = 0.0;


        /** @var ValidationPeriod $periodValidation */
        foreach ($periodValidations as $periodValidation) {
            $data = $periodValidation->json();
            if ($periodValidation->getObjectId() > 0) {
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($periodValidation->getObjectId());
                $label = (string)$activity;
                $data['label'] = "Déclaration pour " . $label;
            }
            $periodValidationsDt[] = $data;
        }

        // Période pas encore commencée
        if ($periodFutur) {
            $periodInfos = "Ce mois n'a pas encore commencé";
        }

        if ($periodFinished) {
            $periodInfos = "Ce mois est terminé";

            // Il y'a des déclarations
            if (count($periodValidations)) {
                $submitable = false;
                $submitableInfos = "Vous avez déja envoyé cette période pour validation";
                $editable = false;
                $editableInfos = "Vous avez déja envoyé cette période pour validation";
            } else {
                $submitable = true;
                $submitableInfos = "Ce mois est terminé, complétez votre déclaration avant de la soumettre";
                $editable = true;
                $editableInfos = "Ce mois est terminé, complétez votre déclaration avant de la soumettre";
            }
        }

        if ($periodCurrent) {
            $periodInfos = "Mois en cours";
            $submitable = false;
            $submitableInfos = "mois en cours, vous ne pouvez soumettre vos déclarations qu'à la fin du mois";
            $editable = true;
            $editableInfos = "Mois en cours, vous pouvez commencer à compléter votre déclaration";
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //// Récupération des lots de travail pour cette période
        $activities = [];
        $workPackages = [];

        $availableWorkPackages = $this->getServiceLocator()->get('ActivityService')->getWorkPackagePersonPeriod($person, $year, $month);
        /** @var WorkPackagePerson $workPackagePerson */
        foreach ($availableWorkPackages as $workPackagePerson) {
            $workPackage = $workPackagePerson->getWorkPackage();
            $activity = $workPackage->getActivity();

            /** @var ValidationPeriod $period */
            $periodActivityValidation = $this->getValidationPeriodActivityAt($workPackage->getActivity(), $person, $year, $month);

            if (!array_key_exists($activity->getId())) {
                $activities[$activity->getId()] = [
                    'id' => $activity->getId(),
                    'acronym' => $activity->getAcronym(),
                    'project' => (string)$activity->getProject(),
                    'project_id' => $activity->getProject()->getId(),
                    'label' => $activity->getLabel(),
                    'total' => 0.0,
                    'validation_state' => $periodActivityValidation ? $periodActivityValidation->json() : null
                ];
            }

            $validationUp = false;

            if ($isPeriodSend) {
                $validationUp = $periodActivityValidation && $periodActivityValidation->isOpenForDeclaration();
            } else {
                $validationUp = true;
            }

            $workPackages[$workPackage->getId()] = [
                'id' => $workPackage->getId(),
                'from' => $from,
                'to' => $to,
                'label' => $workPackage->getLabel(),
                'code' => $workPackage->getCode(),
                'acronym' => $activity->getAcronym(),
                'description' => 'Lot dans ' . (string)$activity,
                'project' => (string)$activity->getProject(),
                'project_id' => $activity->getProject()->getId(),
                'activity' => (string)$activity,
                'activity_id' => $activity->getId(),
                'hours' => $workPackagePerson->getDuration(),
                'total' => 0.0,
                'validation_up' => $validationUp, //!$period || $period->isOpenForDeclaration(),
                'validation_state' => $periodActivityValidation ? $periodActivityValidation->json() : null
            ];
        }

        $periodValidations = $this->getPeriodValidation($person, $month, $year);
        $periodValidationsDt = [];
        /** @var ValidationPeriod $periodValidation */
        foreach ($periodValidations as $periodValidation) {
            $data = $periodValidation->json();

            if ($periodValidation->getObjectId() > 0) {
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($periodValidation->getObjectId());
                $label = (string)$activity;
                $data['label'] = "Déclaration pour " . $label;
            }
            $periodValidationsDt[] = $data;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// Récupération des créneaux Hors-Lot
        $others = $this->getOthersWP();
        $horsLot = [];

        foreach ($others as $key => $datas) {
            $periodHL = $this->getValidationPeriosOutOfWorkpackageAt($person, $year, $month, $key);

            if ($isPeriodSend) {
                $validationUp = $periodHL && $periodHL->isOpenForDeclaration();
            } else {
                $validationUp = true;
            }

            $others[$key]['validation_state'] = $periodHL ? $periodHL->json() : null;
            $others[$key]['validation_up'] = $validationUp;
            $others[$key]['total'] = 0.0;
        }


        foreach ($daysInfos as $day => &$daydata) {
            $datetime = new \DateTime($daydata['datefull']);
            $daydata['date'] = $daydata['datefull'];
            $daydata['data'] = $daydata['datefull'];
            $daydata['i'] = $day;
            $daydata['day'] = $datetime->format('N');
            $daydata['week'] = $datetime->format('W');
            $daydata['editable'] = $editable;
            $daydata['declarations'] = [];
            $daydata['validations'] = null;
            $daydata['total'] = 0.0;
            $output['days'][$day] = $daydata;
            if ($datetime > $now) {
                $daydata['locked'] = true;
            }
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $timesheets = $this->getTimesheetsPersonPeriod($person, $from, $to);
        /** @var TimeSheet $t */
        foreach ($timesheets as $t) {

            $dayInt = (int)$t->getDateFrom()->format('d');


            if (!$t->getActivity()) {
                $datas = [
                    'id' => $t->getId(),
                    'int' => $dayInt,
                    'label' => $this->getOthersWPByCode($t->getLabel())['label'],
                    'code' => $t->getLabel(),
                    'description' => $t->getComment(),
                    'duration' => $t->getDuration(),
                    'status_id' => $t->getValidationPeriod() ? $t->getValidationPeriod()->getStatus() : 'draft',
                    'validations' => $t->getValidationPeriod() ? $t->getValidationPeriod()->json() : null
                ];

                $duree = (float)$t->getDuration();
                $daysInfos[$dayInt]['othersWP'][] = $datas;
                $daysInfos[$dayInt]['duration'] += $duree;
                $daysInfos[$dayInt]['total'] += $duree;
                $periodTotal += $duree;
                $others[$key]['total'] += $duree;
                continue;
            }

            $projectAcronym = $t->getActivity()->getAcronym();
            $project = $t->getActivity()->getProject();
            $project_id = $t->getActivity()->getProject()->getId();
            $activity = $t->getActivity();
            $activity_id = $t->getActivity()->getId();
            $activityCode = $activity->getOscarNum();
            $workpackage = $t->getWorkpackage();
            $wpCode = $workpackage->getCode();
            $periodDeclarations += (float)$t->getDuration();

            $daysInfos[$dayInt]['duration'] += (float)$t->getDuration();
            $daysInfos[$dayInt]['total'] += (float)$t->getDuration();
            $periodTotal += (float)$t->getDuration();

            $activities[$activity->getId()]['total'] += $t->getDuration();
            $workPackages[$workpackage->getId()]['total'] += $t->getDuration();


            if ( !$t->getValidationPeriod() == null ) {
                $output['hasUnsend'] = true;
            }

            $daysInfos[$dayInt]['declarations'][] = [
                'id' => $t->getId(),
                'credentials' => $this->resolveTimeSheetCredentials($t),
                'validations' => $this->resolveTimeSheetValidation($t),
                'label' => $t->getLabel(),
                'comment' => $t->getComment(),
                'activity_id' => $activity_id,
                'activity' => (string)$activity,
                'activity_code' => $activityCode,
                'acronym' => $projectAcronym,
                'project' => (string)$project,
                'project_id' => $project_id,
                'status_id' => $t->getValidationPeriod() ? $t->getValidationPeriod()->getStatus() : 'draft',
                'status' => 'locked',
                'wpCode' => $wpCode,
                'duration' => (float)$t->getDuration(),
                'wp_id' => $t->getWorkpackage()->getId(),
            ];
        }


        $output = [
            'feries' => $this->getLockedDays($year, $month),
            'person' => (string)$person,
            'person_id' => $person->getId(),
            'period' => $periodFirstDay->format('Y-m'),
            'periodMax' => $periodMax,
            'periodInfos' => $periodInfos,
            'periodFutur' => $periodFutur,
            'periodFinished' => $periodFinished,
            'periodCurrent' => $periodCurrent,
            'periodLength' => $periodLength,
            'total' => $periodTotal,
            'periodOpened' => $periodOpened,
            'periodDeclarations' => $periodDeclarations,
            'periodsValidations' => $periodValidationsDt,
            'month' => $month,
            'year' => $year,
            'from' => $periodFirstDay->format('Y-m-d'),
            'to' => $periodLastDay->format('Y-m-d'),
            'submitable' => $submitable,
            'submitableInfos' => $submitableInfos,
            'editable' => $editable,
            'editableInfos' => $editableInfos,
            'period_total_days' => $totalDays,
            'dayNbr' => $totalDays,
            'dayLength' => $this->getOscarConfig()->getConfiguration('declarationsDurations.dayLength.value'),
            'dayExcess' => $this->getOscarConfig()->getConfiguration('declarationsDurations.dayLength.max'),
            'weekExcess' => $this->getOscarConfig()->getConfiguration('declarationsDurations.weekLength.max'),
            'monthExcess' => $this->getOscarConfig()->getConfiguration('declarationsDurations.monthLength.max'),
            'activities' => $activities,
            'workpackages' => $workPackages,
            'otherWP' => $others,
            'declarationInHours' => $declarationInHours,
            'days' => $daysInfos
        ];

        return $output;
    }


    public function getAllTimesheetTypes(Person $person)
    {
        $all = [];
        foreach ($this->getOthersWP() as $other) {
            $all[] = [
                'wp_id' => null,
                'wp_code' => null,
                'acronym' => null,
                'code' => $other['code'],
                'label' => $other['label'],
                'description' => $other['description'],
                'icon' => true,
            ];
        }

        $wps = $this->getEntityManager()->getRepository(WorkPackagePerson::class)->createQueryBuilder('wpp')
            ->where('wpp.person = :personId')
            ->setParameter('personId', $person->getId())
            ->getQuery()
            ->getResult();

        /** @var WorkPackagePerson $wp */
        foreach ($wps as $wp) {
            $all[] = [
                'wp_id' => $wp->getWorkPackage()->getId(),
                'wp_code' => $wp->getWorkPackage()->getCode(),
                'acronym' => $wp->getWorkPackage()->getActivity()->getAcronym(),
                'code' => null,
                'label' => sprintf('[%s] %s', $wp->getWorkPackage()->getActivity()->getAcronym(), $wp->getWorkPackage()->getCode()),
                'description' => sprintf('[%s] %s : %s', $wp->getWorkPackage()->getActivity()->getAcronym(), $wp->getWorkPackage()->getCode(), $wp->getWorkPackage()->getLabel()),
                'icon' => true,
            ];
        }

        return $all;
    }


    public function getPersonTimesheetsDatas(Person $person, $period, $validatedOnly = false)
    {
        $periodBounds = DateTimeUtils::periodBounds($period);

        $query = $this->getEntityManager()->getRepository(TimeSheet::class)
            ->createQueryBuilder('t')
            ->where('t.person = :person')
            ->andWhere('t.dateFrom >= :dateFrom AND t.dateTo <= :dateTo')
            ->setParameters([
                'person' => $person,
                'dateFrom' => $periodBounds['start'],
                'dateTo' => $periodBounds['end'],
            ]);

        $declarations = [
            'activities' => [],
            'others' => []
        ];
        $others = $this->getOthersWP();

        /** @var TimeSheet $timesheet */
        foreach ($query->getQuery()->getResult() as $timesheet) {
            $group = 'Projet inconnue';
            $groupId = null;
            $subGroup = $timesheet->getLabel();
            $subGroupId = 'invalid ID';
            $subGroupType = 'invalid Type';
            $day = $timesheet->getDateFrom()->format('d');

            if ($timesheet->getActivity()) {
                $path = 'activities';
                $group = $timesheet->getActivity()->getAcronym() . " : " . $timesheet->getActivity()->getLabel();
                $groupType = 'activity';
                $groupId = $timesheet->getActivity()->getId();
                $subGroup = sprintf('%s - %s', $timesheet->getWorkpackage()->getCode(), $timesheet->getWorkpackage()->getLabel());
                $subGroupId = $timesheet->getWorkpackage()->getId();
                $subGroupType = "wp";
            }

            if (array_key_exists($timesheet->getLabel(), $others)) {
                $path = 'others';
                $group = 'Hors-lot';
                $groupId = -1;
                $groupType = 'others';
                $subGroup = $others[$timesheet->getLabel()]['label'];
                $subGroupId = $timesheet->getLabel();
                $subGroupType = $timesheet->getLabel();
            }

            if (!array_key_exists($group, $declarations[$path])) {
                $declarations[$path][$group] = [
                    'label' => $group,
                    'id' => $groupId,
                    'type' => $groupType,
                    'total' => 0.0,
                    'subgroup' => [],
                ];
            }

            if (!array_key_exists($subGroup, $declarations[$path][$group]['subgroup'])) {
                $declarations[$path][$group]['subgroup'][$subGroup] = [
                    'label' => $subGroup,
                    'id' => $subGroupId,
                    'type' => $subGroupType,
                    'total' => 0.0,
                    'days' => [],
                ];
            }

            if (!array_key_exists($day, $declarations[$path][$group]['subgroup'][$subGroup]['days'])) {
                $declarations[$path][$group]['subgroup'][$subGroup]['days'][$day] = 0.0;
            }

            $declarations[$path][$group]['subgroup'][$subGroup]['days'][$day] += $timesheet->getDuration();
            $declarations[$path][$group]['subgroup'][$subGroup]['days']['total'] += $timesheet->getDuration();
            $declarations[$path][$group]['total'] += $timesheet->getDuration();

        }

        $output = [
            'person' => (string)$person,
            'person_id' => $person->getId(),
            'period' => $period,
            'totalDays' => $periodBounds['totalDays'],
            'daysInfos' => $this->getDaysPeriodInfosPerson($person, $periodBounds['year'], $periodBounds['month']),
            'declarations' => $declarations,
        ];


        return $output;
    }


    private $_cacheValidationsPeriodPerson = [];


    /**
     * Retourne l'état général de la période pour la personne.
     *
     * @param Person $person
     * @param $periodKey
     * @return mixed
     * @throws OscarException
     */
    public function getValidationStatePersonPeriod(Person $person, $periodKey)
    {
        $periodData = DateTimeUtils::extractPeriodDatasFromString($periodKey);
        $year = $periodData['year'];
        $month = $periodData['month'];
        $key = sprintf('person_%s-period_%s-%s', $person->getId(), $year, $month);

        if (!array_key_exists($key, $this->_cacheValidationsPeriodPerson)) {
            $validations = $this->getValidationPeriods($year, $month, $person);

            $states = ["unsend", ValidationPeriod::STATUS_VALID, ValidationPeriod::STATUS_STEP1, ValidationPeriod::STATUS_STEP2, ValidationPeriod::STATUS_STEP3, ValidationPeriod::STATUS_CONFLICT];
            $globalState = 0;

            $datas = [
                'state' => "",
                'validations' => []
            ];

            /** @var ValidationPeriod $vp */
            foreach ($validations as $vp) {
                $datas['validations'][] = (string)$vp;
                $globalState = max($globalState, array_search($vp->getStatus(), $states));
            }

            $datas['state'] = $states[$globalState];

            $this->_cacheValidationsPeriodPerson[$key] = $datas;
        }
        return $this->_cacheValidationsPeriodPerson[$key];

    }

    public function getValidationHorsLotByReferent(Person $referent)
    {

        $validations = [];

        if ($referent) {

            $subordinates = $this->getServiceLocator()->get('PersonService')->getSubordinates($referent);


            if (count($subordinates))
                $validations = $this->getEntityManager()->getRepository(ValidationPeriod::class)->createQueryBuilder('vp')
                    ->where('vp.declarer IN(:persons)')
                    ->setParameter('persons', $subordinates)
                    ->getQuery()
                    ->getResult();
        }


        return $validations;
    }

    /**
     * Retourne les créneaux de la personne regroupès par activité
     * @param Person $person
     */
    public function getPersonTimesheets(Person $person, $validatedOnly = false, $periodFilter = null, $activity = null)
    {

        $query = $this->getEntityManager()->getRepository(TimeSheet::class)
            ->createQueryBuilder('t')
            ->where('t.person = :person')
            ->orderBy('t.activity, t.dateFrom')
            ->setParameter('person', $person);

        if ($activity != null) {
            $query->andWhere('t.activity = :activity')
                ->setParameter('activity', $activity);
        }

        $datas = [];

        /** @var TimeSheet $timesheet */
        foreach ($query->getQuery()->getResult() as $timesheet) {
            if (!$timesheet->getActivity()) continue;

            $activityId = $timesheet->getActivity()->getId();
            $period = $timesheet->getDateFrom()->format('Y-m');
            $periodKey = $timesheet->getDateFrom()->format('Y-n');

            if ($periodFilter !== null && $periodFilter != $period)
                continue;

            $validationState = $this->getValidationStatePersonPeriod($person, $periodKey);

            if (!array_key_exists($activityId, $datas)) {

                $datas[$activityId] = [
                    'activityObj' => (string)$timesheet->getActivity(),
                    'activity' => (string)$timesheet->getActivity(),
                    'project' => (string)$timesheet->getActivity()->getProject(),
                    'activity_id' => $timesheet->getActivity()->getId(),
                ];
            }

            if (!array_key_exists($period, $datas[$activityId])) {


                $datas[$activityId][$period] = [
                    'toto' => 'tata',
                    'unvalidate' => $validationState['state'],
                    'total' => 0.0,
                ];
                /** @var WorkPackage $wp */
                foreach ($timesheet->getActivity()->getWorkPackages() as $wp) {
                    if (!array_key_exists($wp->getCode(), $datas[$activityId]['timesheets'][$period]))
                        $datas[$activityId]['timesheets'][$period][$wp->getCode()] = [
                            'total' => 0.0,
                            'unvalidate' => $validationState['state'],
                        ];
                }
            }
            $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()]['total'] += $timesheet->getDuration();
            $datas[$activityId]['timesheets'][$period]['total'] += $timesheet->getDuration();


            $day = (string)$timesheet->getDateFrom()->format('j');
            if (!array_key_exists($day, $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()])) {
                $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()][$day] = 0.0;
                $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()]["crenaux_" . $day] = 0;
            }
            $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()]["crenaux_" . $day]++;
            $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()][$day] += $timesheet->getDuration();

        }

        return $datas;
    }

    /**
     * Retourne la liste des déclarants
     */
    public function getDeclarersList()
    {

        $persons = $this->getEntityManager()->createQueryBuilder()->select('p')
            ->from(Person::class, 'p')
            ->orderBy('p.lastname', 'ASC')
            ->innerJoin('p.workPackages', 'wp');

        $output = [];

        /** @var ValidationPeriodRepository $validationsPeriodRepo */
        $validationsPeriodRepo = $this->getEntityManager()->getRepository(ValidationPeriod::class);

        /** @var Person $person */
        foreach ($persons->getQuery()->getResult() as $person) {
            $personData = $person->toJson();
            $personData['workpackages'] = count($person->getWorkPackages());
            $personData['validationsStats'] = $validationsPeriodRepo->getValidationPersonStats($person);
            //$personData['predictedPeriods'] = $validationsPeriodRepo->getPredictedPeriods($person);
            $personData['periods'] = $validationsPeriodRepo->getPredictedPeriodsPack($person);
            $output[] = $personData;
        }

        return $output;
    }

    /**
     * Retourne les données sur les déclarations de la personne
     */
    public function getDeclarerDetails(Person $person)
    {
        throw new OscarException("PAS FAIT");
    }


    public function getDeclarers()
    {
        // Récupération des IDS des déclarants
        $timesheets = $this->getTimesheetRepository()->getTimesheetsWithWorkPackage();
        $out = [];
        $persons = [];
        $activities = [];
        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {
            /** @var Person $currentPerson */
            $currentPerson = $timesheet->getPerson();

            if (!array_key_exists($currentPerson->getId(), $persons)) {
                $persons[$currentPerson->getId()] = $currentPerson->toJson();
                $persons[$currentPerson->getId()]['declarations'] = [];
            }

            /** @var Activity $currentActivity */
            $currentActivity = $timesheet->getActivity();

            if (!array_key_exists($currentActivity->getId(), $persons[$currentPerson->getId()]['declarations'])) {
                $persons[$currentPerson->getId()]['declarations'][$currentActivity->getId()] = $currentActivity->toJson();
                $persons[$currentPerson->getId()]['declarations'][$currentActivity->getId()]['timesheets'] = [];
            }

            $persons[$currentPerson->getId()]['declarations'][$currentActivity->getId()]['timesheets'][] = $timesheet->toJson();

            $out[] = $timesheet->toJson();

        }
        return [
            'persons' => $persons,
        ];
    }


    public function getActivitiesWithTimesheetSend()
    {

        // récupération des périodes
        /** @var ValidationPeriodRepository $repositoryPeriod */
        $repositoryPeriod = $this->getEntityManager()->getRepository(ValidationPeriod::class);

        $activitiesIdsWithValidationProgress = $repositoryPeriod->getValidationPeriodsValidationProgressActivitiesIds();

        $activities = $this->getEntityManager()->createQueryBuilder()->select('a')
            ->from(Activity::class, 'a')
            ->where('a.id IN (:ids)');

        return $activities->setParameter('ids', $activitiesIdsWithValidationProgress)->getQuery()->getResult();
    }

    public function getTimesheetRejected(Person $person)
    {
        $timesheets = $this->getEntityManager()->createQueryBuilder()->select('t')
            ->from(TimeSheet::class, 't')
            ->where('t.person = :person AND t.status = :status')
            ->setParameter('person', $person)
            ->setParameter('status', TimeSheet::STATUS_CONFLICT);
        return $timesheets->getQuery()->getResult();
    }

    public function getTimesheetsPersonPeriodArrayId($currentPerson, \DateTime $from, \DateTime $to)
    {

        $query = $this->getEntityManager()->createQueryBuilder('t')
            ->select('t.id')
            ->from(TimeSheet::class, 't')
            ->where('t.person = :owner AND t.status = :status AND t.dateFrom >= :from AND t.dateTo <= :to')
            ->setParameters([
                'owner' => $currentPerson,
                'from' => $from,
                'to' => $to->format('Y-m-d'),
                'status' => TimeSheet::STATUS_DRAFT,
            ])
            ->getQuery();
        return $query->getArrayResult();
    }

    /***
     * @param $currentPerson
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Query
     */
    protected function getQueryTimesheetsPersonPeriod($currentPerson, \DateTime $from, \DateTime $to)
    {
        $query = $this->getEntityManager()->createQueryBuilder('t')
            ->select('t')
            ->from(TimeSheet::class, 't')
            ->where('t.person = :owner AND t.dateFrom >= :from AND t.dateTo <= :to')
            ->setParameters([
                'owner' => $currentPerson,
                'from' => $from,
                'to' => $to
            ])
            ->getQuery();

        return $query;
    }

    public function setTimesheetToSend(TimeSheet &$timeSheet)
    {
        $timeSheet->setStatus(TimeSheet::STATUS_TOVALIDATE)
            ->setSendBy((string)$this->getOscarUserContext()->getCurrentPerson())
            ->setRejectedSciComment(null)
            ->setRejectedSciAt(null)
            ->setRejectedSciBy(null)
            ->setRejectedSciById(null)
            ->setRejectedAdminComment(null)
            ->setRejectedAdminAt(null)
            ->setRejectedAdminBy(null)
            ->setRejectedAdminById(null)
            ->setValidatedSciAt(null)
            ->setValidatedSciBy(null)
            ->setValidatedSciById(null)
            ->setValidatedAdminAt(null)
            ->setValidatedAdminBy(null)
            ->setValidatedAdminById(null);
    }


    /**
     * Envoi des déclarations.
     *
     * @param $data TimeSheet|TimeSheet[]
     * @param $by
     * @throws OscarException
     */
    public function send($datas, $by)
    {
        $timesheets = [];
        if (!$datas) {
            throw new \Exception("Invalid datas");
        }

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get('NotificationService');
        $activityNotification = [];

        foreach ($datas as $data) {
            if ($data['id']) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                $this->setTimesheetToSend($timeSheet);

                $activityNotification[$timeSheet->getActivity()->getId()] = $timeSheet->getActivity();

                $this->getEntityManager()->flush($timeSheet);
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;
            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }

        try {
            $notificationService->notifyActivitiesTimesheetSend($activityNotification);
        } catch (\Exception $e) {
            //$this->getServiceLocator()->get('Logger')->error($e->getMessage() ." - " . $e->getTraceAsString());
        }
        return $timesheets;
    }

    /**
     * Retourne la liste des déclaration de la personne.
     *
     * @param Person $person
     * @return array
     */
    function allByPerson(Person $person, $personCredentials = null)
    {
        $timesheets = [];
        $datas = $this->getEntityManager()->getRepository(TimeSheet::class)->findBy(['person' => $person]);

        /** @var TimeSheet $data */
        foreach ($datas as $data) {
            $json = $data->toJson();
            $json['credentials'] = $this->resolveTimeSheetCredentials($data, $personCredentials);
            $timesheets[] = $json;
        }

        foreach ($this->getExternal($person) as $data) {
            $timesheets[] = $data;
        }

        return $timesheets;
    }

    /**
     * Charge les emplois du temps "extérieurs"
     * @param Person $person
     */
    public function getExternal(Person $person)
    {
        // TODO Récupération des créneaux 'externes'
        return [/*
            [
                'id'                => null,
                'activity_id'       => null,
                'activity_label'    => null,
                'label'             => 'conges',
                'status'             => 'info',
                'owner'    => "Stéphane Bouvry",
                'owner_id'    => 5063,
                'start' => "2018-04-05T09:00:00+02:00",
                'end' => "2018-04-05T16:30:00+02:00",
                'credentials'    => [
                    'deletable' => false,
                    'editable' => false,
                    'sendable' => false,
                    'validableAdm' => false,
                    'validableSci' => false,
                ],
            ]*/
        ];
    }

    /**
     * @return PersonService
     */
    protected function getPersonService()
    {
        return $this->getServiceLocator()->get('PersonService');
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getServiceLocator()->get('Logger');
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// TRAITMENT DES PÉRIODES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Retourne les procédures de validation pour la période et la personne données.
     *
     * @param $year
     * @param $month
     * @param Person $person
     * @return array
     */
    public function getValidationPeriods($year, $month, Person $person)
    {
        $query = $this->getEntityManager()->getRepository(ValidationPeriod::class)->createQueryBuilder('v')
            ->where('v.year = :year AND v.month = :month AND v.declarer = :person')
            ->setParameters([
                'year' => $year,
                'month' => $month,
                'person' => $person,
            ])
            ->getQuery();
        return $query->getResult();
    }

    public function sendPeriod($from, $to, $sender)
    {

        $fromMonth = $from->format('Y-m');
        $toMonth = $to->format('Y-m');

        if ($fromMonth != $toMonth)
            throw new Exception("La période à traiter n'est pas un mois...");

        $mois = (integer)$from->format('m');
        $annee = (integer)$from->format('Y');

        // Créneaux de la périodes
        $timesheets = $this->getQueryTimesheetsPersonPeriod($sender, $from, $to)->getResult();

        if (count($timesheets) == 0) {
            throw new OscarException("Aucun créneau à soumettre pour cette période.");
        }

        // Déclarations de la période
        $declarations = $this->getValidationPeriods($annee, $mois, $sender);

        if (count($declarations) > 0) {
            throw new OscarException("Vous avez déjà envoyé des déclarations pour cette période");
        }

        $declarations = [];

        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {

            $objectGroup = ValidationPeriod::GROUP_OTHER;
            $object = $timesheet->getLabel();
            $objectId = -1;

            if ($timesheet->getActivity()) {
                $object = ValidationPeriod::OBJECT_ACTIVITY;
                $objectGroup = ValidationPeriod::GROUP_WORKPACKAGE;
                $objectId = $timesheet->getActivity()->getId();
            }

            $key = sprintf("%s_%s", $object, $objectId);
            if (!array_key_exists($key, $declarations)) {
                $declarations[$key] = [
                    'objectId' => $objectId,
                    'object' => $object,
                    'objectGroup' => $objectGroup,
                    'log' => "Déclaration envoyée"
                ];
                $declarations[$key]['declaration'] = $this->createDeclaration($sender, $annee, $mois, $object, $objectId, $objectGroup);
            }
            $timesheet->setValidationPeriod($declarations[$key]['declaration']);
        }

        $this->getEntityManager()->flush($timesheets);
    }

    public function getValidatorsPrj(Activity $activity)
    {
        return $this->getPersonService()->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ACTIVITY, $activity);
    }

    public function getValidatorsSci(Activity $activity)
    {
        return $this->getPersonService()->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI, $activity);
    }

    public function getValidatorsAdm(Activity $activity)
    {
        return $this->getPersonService()->getAllPersonsWithPrivilegeInActivity(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM, $activity);
    }


    public function isValidator( Person $person ){
        $query = $this->getEntityManager()->getRepository(ValidationPeriod::class)
            ->createQueryBuilder('vp')
            ->leftJoin('vp.validatorsPrj', 'vprj')
            ->leftJoin('vp.validatorsSci', 'vsci')
            ->leftJoin('vp.validatorsAdm', 'vadm')
            ->where('vprj = :person OR vsci = :person OR vadm = :person')
            ->setParameter('person', $person);

        return count($query->getQuery()->getResult()) > 0;
    }

    /**
     * Retourne toutes les validations où la personne est identifiée comme validateur.
     *
     * @param Person $person
     * @return array
     */
    public function getValidationToDoPerson(Person $person)
    {

        $query = $this->getEntityManager()->getRepository(ValidationPeriod::class)
            ->createQueryBuilder('vp')
            ->leftJoin('vp.validatorsPrj', 'vprj')
            ->leftJoin('vp.validatorsSci', 'vsci')
            ->leftJoin('vp.validatorsAdm', 'vadm')
            ->where('vprj = :person OR vsci = :person OR vadm = :person')
            ->setParameter('person', $person);

        $validations = [];

        /** @var ValidationPeriod $validation */
        foreach ($query->getQuery()->getResult() as $validation) {
            if ($validation->isValidator($person)) {
                $validations[] = $validation;
            }
        }

        return $validations;
    }

    public function getInvalidLabels(){
        $output = [];

        $horsLots = [];
        foreach ($this->getOthersWP() as $other) {
            $horsLots[] = $other['code'];
        }

        //
        $query = $this->getEntityManager()->getRepository(TimeSheet::class)->createQueryBuilder('t')
            ->select('DISTINCT t.label')
            ->where('t.label NOT IN(:othersWP) AND t.activity IS NULL')
            ->getQuery()
            ->setParameters([
                'othersWP' => $horsLots
            ]);

        foreach( $query->getArrayResult() as $d ){
            $output[] = $d['label'];

        }

        return $output;
    }

    public function maintenanceConvertHorsLots( $correspondances ){

        $labels = array_keys($correspondances);
        $query = $this->getEntityManager()->getRepository(TimeSheet::class)->createQueryBuilder('t')
            ->where('t.label IN(:othersWP) AND t.activity IS NULL')
            ->getQuery()
            ->setParameters([
                'othersWP' => $labels
            ]);
        /** @var TimeSheet $timesheet */
        foreach ($query->getResult() as $timesheet){
            $oldLabel = $timesheet->getLabel();
            $newLabel = $correspondances[$oldLabel];

            $timesheet->setLabel($newLabel)
                ->setComment($timesheet->getComment() . " " . $oldLabel);
        }

        $this->getEntityManager()->flush();
    }

    public function validation(ValidationPeriod $period, Person $validateur, $message = "")
    {
        if ($period->isValidator($validateur)) {

            if($period->getObject() == ValidationPeriod::OBJECT_ACTIVITY){
                $obj = $this->getEntityManager()->getRepository(Activity::class)->find($period->getObjectId())->log();
            } else {
                $obj = $period->getLabel();
            }
            switch ($period->getStatus()) {
                case ValidationPeriod::STATUS_STEP1:
                    $msg = sprintf("a validé niveau activité la déclartion %s", $obj);
                    $period->setValidationActivity($validateur, new \DateTime(), $message);
                    $period->addLog('vient de valider niveau activité la déclaration.', (string)$validateur);
                    break;

                case ValidationPeriod::STATUS_STEP2:
                    $msg = sprintf("a validé scientifiquement la déclartion %s", $obj);
                    $period->setValidationSci($validateur, new \DateTime(), $message);
                    $period->addLog('vient de valider scientifiquement la déclaration.', (string)$validateur);
                    break;

                case ValidationPeriod::STATUS_STEP3:
                    $msg = sprintf("a validé administrativement la déclartion %s", $obj);
                    $period->setValidationAdm($validateur, new \DateTime(), $message);
                    $period->addLog('vient de valider administrativement la déclaration.', (string)$validateur);
                    break;

                default:
                    throw new OscarException("Cette période n'a pas le bon status pour être validée.");
            }

            /** @var ActivityLogService $als */
            $als = $this->getServiceLocator()->get('ActivityLogService');
            $als->addUserInfo($msg, 'Activity', $period->getObjectId());
            $this->getEntityManager()->flush($period);
            $this->notificationsValidationPeriod($period);
            return true;
        } else {
            throw new OscarException("Vous n'êtes pas autorisé à valider pour cette étape de validation");
        }
    }

    public function reject(ValidationPeriod $period, Person $validateur, $message = "")
    {

        if($period->getObject() == ValidationPeriod::OBJECT_ACTIVITY){
            $obj = $this->getEntityManager()->getRepository(Activity::class)->find($period->getObjectId())->log();
        } else {
            $obj = $period->getLabel();
        }

        if ($period->isValidator($validateur)) {
            switch ($period->getStatus()) {
                case ValidationPeriod::STATUS_STEP1:
                    $msg = sprintf("a rejeté niveau activité la déclartion %s", $obj);
                    $period->setRejectActivity($validateur, new \DateTime(), $message);
                    $period->addLog('vient de rejeter niveau activité la déclaration.', (string)$validateur);
                    break;

                case ValidationPeriod::STATUS_STEP2:
                    $msg = sprintf("a rejeté scientifiquement la déclartion %s", $obj);
                    $period->setRejectSci($validateur, new \DateTime(), $message);
                    $period->addLog('vient de rejeter scientifiquement la déclaration.', (string)$validateur);
                    break;

                case ValidationPeriod::STATUS_STEP3:
                    $msg = sprintf("a rejeté administrativement la déclartion %s", $obj);
                    $period->setRejectAdm($validateur, new \DateTime(), $message);
                    $period->addLog('vient de rejeter administrativement la déclaration.', (string)$validateur);
                    break;

                default:
                    throw new OscarException("Cette période n'a pas le bon status pour être validée.");
            }
            /** @var ActivityLogService $als */
            $als = $this->getServiceLocator()->get('ActivityLogService');
            $als->addUserInfo($msg, 'Activity', $period->getObjectId());

            $this->getEntityManager()->flush($period);
            $this->notificationsValidationPeriod($period);
            return true;
        } else {
            throw new OscarException("Vous n'êtes pas autorisé à valider pour cette étape de validation");
        }
    }

    /**
     * @param ValidationPeriod $validationPeriod
     * @throws OscarException
     */
    public function notificationsValidationPeriod(ValidationPeriod $validationPeriod)
    {
        $notificationService = $this->getServiceNotification();

        if ($validationPeriod->hasConflict()) {
            $notificationService->notification(_("Déclaration rejetée"),
                [$validationPeriod->getDeclarer()],
                'ValidationPeriod-reject',
                $validationPeriod->getId(),
                'Application',
                new \DateTime(),
                new \DateTime());
        } elseif ($validationPeriod->getStatus() == ValidationPeriod::STATUS_VALID) {
            $notificationService->notification(_("Déclaration validée"),
                [$validationPeriod->getDeclarer()],
                'ValidationPeriod-valid',
                $validationPeriod->getId(),
                'Application',
                new \DateTime(),
                new \DateTime());
        } else {
            $notificationService->notification(_("Validation en attente"),
                $validationPeriod->getCurrentValidators()->toArray(),
                'ValidationPeriod-wait',
                $validationPeriod->getId(),
                'Application',
                new \DateTime(),
                new \DateTime());
        }
    }

    /**
     * @param $sender
     * @param $year
     * @param $month
     * @param $object
     * @param $objectId
     * @param $objectGroup
     * @return ValidationPeriod
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function createDeclaration($sender, $year, $month, $object, $objectId, $objectGroup)
    {

        $declaration = new ValidationPeriod();

        // CAS N°1 : Validation Hors-Lot
        if ($objectGroup == ValidationPeriod::GROUP_OTHER) {
            $validateurs = $this->getPersonService()->getManagers($sender);

            // ETAPE 3 Directement
            $declaration->setValidationActivityById(-1)
                ->setValidationActivityAt(new \DateTime())
                ->setValidationActivityMessage("Validation automatique pour les créneaux hors-lot")
                ->setValidationActivityBy('Oscar Bot');

            $declaration->setValidationSciById(-1)
                ->setValidationSciAt(new \DateTime())
                ->setValidationSciMessage("Validation automatique pour les créneaux hors-lot")
                ->setValidationSciBy('Oscar Bot');

            $declaration->setStatus(ValidationPeriod::STATUS_STEP3);

            /** @var Person $validateur */
            foreach ($validateurs as $validateur) {
                $declaration->addValidatorAdm($validateur);
            }
        } else {
            /** @var Activity $activity */
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($objectId);


            $validateursPrj = $this->getValidatorsPrj($activity);
            /** @var Person $validateur */
            foreach ($validateursPrj as $validateur) {
                $declaration->addValidatorPrj($validateur);
            }

            $validateursAdm = $this->getValidatorsAdm($activity);
            /** @var Person $validateur */
            foreach ($validateursAdm as $validateur) {
                $declaration->addValidatorAdm($validateur);
            }

            $validateursSci = $this->getValidatorsSci($activity);
            /** @var Person $validateur */
            foreach ($validateursSci as $validateur) {
                $declaration->addValidatorSci($validateur);
            }

            $declaration->setStatus(ValidationPeriod::STATUS_STEP1);
        }

        $settings = json_encode($this->getDayLengthPerson($sender));

        $now = new \DateTime();
        $declaration
            ->setSchedule($settings)
            ->setDateSend($now)
            ->setDeclarer($sender)
            ->setLog($now->format('Y-m-d H:i:s') . " : $sender vient d'envoyer sa déclaration\n")
            ->setObject($object)
            ->setObjectId($objectId)
            ->setObjectGroup($objectGroup)
            ->setYear($year)
            ->setMonth($month);
        $this->getEntityManager()->persist($declaration);
        $this->getEntityManager()->flush($declaration);

        $this->notificationsValidationPeriod($declaration);

        return $declaration;

    }

    public function allByActivity(Activity $activity)
    {
        $timesheets = $this->getEntityManager()->createQueryBuilder()
            ->select('t')
            ->from(TimeSheet::class, 't')
            ->where('t.activity = :activity')
            ->setParameters([
                'activity' => $activity
            ])
            ->getQuery()
            ->getResult();

        $declaration = [];
        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {
            $json = $timesheet->toJson();
            $json['credentials'] = $this->resolveTimeSheetCredentials($timesheet);
            $declaration[] = $json;
        }

        return $declaration;
    }

    public function resolveTimeSheetValidation(TimeSheet $timeSheet)
    {
        $validation = false;

        if ($timeSheet->getStatus() != TimeSheet::STATUS_INFO) {

            $conflict = '';

            if ($timeSheet->getRejectedAdminAt()) {
                $conflict .= sprintf("Rejet administratif par %s le %s : %s", $timeSheet->getRejectedAdminBy(), $timeSheet->getRejectedAdminAt()->format('Y-m-d'), $timeSheet->getRejectedAdminComment());
            }
            if ($timeSheet->getRejectedSciAt()) {
                $conflict .= sprintf("Rejet scientifique par %s le %s : %s", $timeSheet->getRejectedSciBy(), $timeSheet->getRejectedSciAt()->format('Y-m-d'), $timeSheet->getRejectedSciComment());
            }

            $validation = [
                'prj' => [
                    'date' => null,
                    'by' => null
                ],
                'sci' => [
                    'date' => $timeSheet->getValidatedSciAt() ? $timeSheet->getValidatedSciAt()->format('Y-m-d') : null,
                    'validator' => $timeSheet->getValidatedSciBy() ? $timeSheet->getValidatedSciBy() : null,
                    'validator_id' => $timeSheet->getValidatedSciById() ? $timeSheet->getValidatedSciById() : null,
                ],
                'adm' => [
                    'date' => $timeSheet->getValidatedAdminAt() ? $timeSheet->getValidatedAdminAt()->format('Y-m-d') : null,
                    'validator' => $timeSheet->getValidatedAdminBy() ? $timeSheet->getValidatedAdminBy() : null,
                    'validator_id' => $timeSheet->getValidatedAdminById() ? $timeSheet->getValidatedAdminById() : null,
                ],
                'conflict' => $conflict ? $conflict : false
            ];
        }
        return $validation;
    }

    /**
     * Résolution des droits sur une déclaration
     * @param TimeSheet $timeSheet
     * @return array
     */
    public function resolveTimeSheetCredentials(TimeSheet $timeSheet, $person = null)
    {
        if ($person == null) {
            $person = $this->getOscarUserContext()->getCurrentPerson();
        }

        /** @var ValidationPeriod $periodValidation */
        $periodValidation = $this->getPeriodValidationTimesheet($timeSheet);


        $deletable = false;
        $isOwner = $timeSheet->getPerson() == $person;

        // Le créneau ne peut être envoyé que par sont propriétaire et si
        // le status est "Bouillon"
        $sendable = $isOwner && $periodValidation == null;

        // Seul les déclarations en brouillon peuvent être éditées
        $editable = $periodValidation == null && $isOwner;

        // Validation scientifique (déja validé ou la personne a les droits)

        $validablePrj = false;
        $validableSci = false;
        $validableAdm = false;

        if ($periodValidation) {
            switch ($periodValidation->getStatus()) {
                case ValidationPeriod::STATUS_STEP1 :
                case ValidationPeriod::STATUS_STEP2 :
                case ValidationPeriod::STATUS_STEP3 :
                    $timeSheet->setStatus(TimeSheet::STATUS_TOVALIDATE);
                    break;
                case ValidationPeriod::STATUS_VALID :
                    $timeSheet->setStatus(TimeSheet::STATUS_ACTIVE);
                    break;
                case ValidationPeriod::STATUS_CONFLICT :
                    $timeSheet->setStatus(TimeSheet::STATUS_CONFLICT);
                    $deletable = true;
                    $editable = true;
                    break;
            }
        } else {
            $deletable = true;
            $editable = true;
        }

        if ($timeSheet->getStatus() == TimeSheet::STATUS_TOVALIDATE) {
            $validableSci = $timeSheet->getValidatedSciAt() ?
                false :
                $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI,
                    $timeSheet->getActivity());

            // Validation administrative
            $validableAdm = $timeSheet->getValidatedAdminAt() ?
                false :
                $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM,
                    $timeSheet->getActivity());
        }

        return [
            'deletable' => $deletable,
            'editable' => $editable,
            'sendable' => $sendable,
            'validablePrj' => $validablePrj,
            'validableSci' => $validableSci,
            'validableAdm' => $validableAdm
        ];
    }

    public function createOrMerge($datas)
    {

    }

    /**
     * Création des déclarations.
     *
     * @param $datas
     * @param $by
     * @return array
     */
    public function create($datas, $by)
    {
        foreach ($datas as $data) {
            if (array_key_exists('id', $data) && $data['id'] != null) {
                $this->getServiceLocator()->get('Logger')->info("MAJ " . $data['id']);
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
            } else {
                $this->getServiceLocator()->get('Logger')->info("ADD " . $data['id']);
                $timeSheet = new TimeSheet();
                $this->getEntityManager()->persist($timeSheet);
            }

            $this->getServiceLocator()->get('Logger')->info("owner " . $by);
            $status = TimeSheet::STATUS_INFO;

            $this->getServiceLocator()->get('Logger')->info(print_r($data, true));

            if (isset($data['idworkpackage']) && $data['idworkpackage'] != 'null') {
                /** @var WorkPackage $workPackage */
                $workPackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($data['idworkpackage']);
                $timeSheet->setWorkpackage($workPackage);
                $status = TimeSheet::STATUS_DRAFT;
            } elseif (isset($data['idactivity']) && $data['idactivity'] != 'null') {
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($data['idactivity']);
                $timeSheet->setActivity($activity);
                $status = TimeSheet::STATUS_DRAFT;
            } else {
                $timeSheet->setWorkpackage(null)->setActivity(null);
            }

            $timeSheet->setComment($data['description'])
                ->setIcsFileUid($data['icsfileuid'] ? $data['icsfileuid'] : '')
                ->setIcsFileDateAdded($data['icsfileuid'] ? new \DateTime() : null)
                ->setIcsFileName($data['icsfilename'] ? $data['icsfilename'] : '')
                ->setIcsUid($data['icsuid'] ? $data['icsuid'] : '')
                ->setLabel($data['label'])
                ->setCreatedBy($by)
                ->setPerson($by)
                ->setStatus($status)
                ->setDateFrom(new \DateTime($data['start']))
                ->setDateTo(new \DateTime($data['end']));

            if ($status == TimeSheet::STATUS_INFO) {
                $timeSheet->setActivity(null)
                    ->setWorkpackage(null)
                    ->setSendBy(null);
                $timeSheet = $this->resetValidationData($timeSheet);
            }

            $json = $timeSheet->toJson();
            $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet, $by);
            $timesheets[] = $json;

        }
        $this->getEntityManager()->flush($timeSheet);

        return $timesheets;
    }


    /**
     * Supprimer les données des champs de validation/rejet dans la déclaration.
     *
     * @param TimeSheet $timeSheet
     * @return TimeSheet
     */
    public function resetValidationData(TimeSheet $timeSheet)
    {
        return $timeSheet
            ->setRejectedAdminById(null)
            ->setRejectedAdminBy(null)
            ->setRejectedAdminAt(null)
            ->setRejectedAdminComment(null)
            ->setRejectedSciAt(null)
            ->setRejectedSciBy(null)
            ->setRejectedSciById(null)
            ->setRejectedSciComment(null)
            ->setRejectedAdminComment(null);
    }

    /**
     * Suppression des créneaux issues de l'ICS.
     *
     * @param $icsUid UID du fichier ICAL source
     * @param $by Person à l'origine de la suppression
     */
    public function deleteIcsFileUid($icsUid, $by)
    {
        // Récupération des créneaux correspondant
        $timesheets = $this->getTimesheetRepository()->getTimesheetsByIcsFileUid($icsUid);


        $this->getServiceLocator()->get('Logger')->info("Nombre de créneaux à traiter : " . count($timesheets));

        // Liste des problèmes
        $warnings = [];

        // Status éligibles à la suppression
        $status = [TimeSheet::STATUS_DRAFT, TimeSheet::STATUS_TOVALIDATE_ADMIN, TimeSheet::STATUS_TOVALIDATE, TimeSheet::STATUS_TOVALIDATE_SCI];


        /** @var TimeSheet $timesheet */
        foreach ($timesheets as $timesheet) {
            if ($timesheet->getPerson() != $by) {
                $warnings[] = sprintf("Le créneau '%s' n'a pas été supprimé (owner error).", $timesheet);
                continue;
            }
            if (!in_array($timesheet->getStatus(), $status)) {
                $warnings[] = sprintf("Le créneau '%s' n'a pas été supprimé (statut error).", $status);
                continue;
            }
            $this->getEntityManager()->remove($timesheet);
        }
        $this->getEntityManager()->flush();
        return $warnings;
    }


    public function getLockedDays($annee, $mois, $loadInitiale = true, $person = null)
    {

        $annee = intval($annee);
        $mois = intval($mois);
        // Jours vérrouillés dans le mois
        $locked = [];

        if ($loadInitiale == true) {
            $datas = $this->getServiceLocator()->get('Configuration');
            $lockedEachMonth = $datas['oscar']['closedDays'];
            $lockedEachSpecifics = $datas['oscar']['closedDaysExtras'];
            $lockedEachMonth($locked, $annee, $mois);
            $lockedEachSpecifics($locked, $annee, $mois);
        }

        return $locked;
    }

    /**
     * @return TimesheetRepository
     */
    protected function getTimesheetRepository()
    {
        return $this->getEntityManager()->getRepository(TimeSheet::class);
    }


    public function getPeriodValidationTimesheet(TimeSheet $t)
    {

        $year = $t->getDateFrom()->format('Y');
        $month = $t->getDateFrom()->format('m');

        /** @var ValidationPeriodRepository $periodRepo */
        $periodRepo = $this->getEntityManager()->getRepository(ValidationPeriod::class);

        if ($t->getActivity()) {
            $period = $periodRepo->getValidationPeriodForActivity($year, $month, $t->getActivity()->getId(), $t->getPerson()->getId());
        } else {
            $period = $periodRepo->getValidationPeriodOutWP($year, $month, $t->getLabel(), $t->getPerson()->getId());
        }
        return $period;
    }

    /**
     * Suppression du créneau.
     *
     * @param $timesheetId Identifiant du créneau à supprimer
     * @param $currentPerson
     * @return bool
     * @throws OscarException
     */
    public function delete($timesheetId, $currentPerson)
    {
        if (!is_array($timesheetId)) {
            $timesheetId = [$timesheetId];
        }

        $timesheets = $this->getEntityManager()->getRepository(TimeSheet::class)
            ->createQueryBuilder('t')
            ->where('t.id IN(:ids)')
            ->setParameter('ids', $timesheetId)
            ->getQuery()->getResult();

        if (!$timesheets) {
            throw new OscarException("Créneau introuvable.");
        }

        //
        try {
            $errors = "";

            /** @var TimeSheet $t */
            foreach ($timesheets as $t) {

                try {
                    /** @var ValidationPeriod $validationPeriod */
                    $validationPeriod = $this->getPeriodValidationTimesheet($t);

                    if ($validationPeriod != null && $validationPeriod->getStatus() != ValidationPeriod::STATUS_CONFLICT) {
                        throw new \Exception("Ce créneau a une procédure de validation active. Vous ne pouvez pas le modifier");
                    }

                    $this->deleteTimesheet($t, $currentPerson, false);
                } catch (\Exception $e) {
                    $errors .= $e->getMessage() . "\n";
                }
            }
            $this->getEntityManager()->flush();

        } catch (\Exception $e) {
            throw new OscarException("BD Error : " . $e->getMessage());
        }

        if ($errors) {
            throw new OscarException("Un ou plusieurs créneaux n'ont pas pu être supprimés : \n" . $errors);
        }

        return true;
    }


    /**
     * @param TimeSheet $timesheet
     * @param null|Person $person
     * @param bool $flush
     * @throws OscarException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteTimesheet(TimeSheet $timesheet, $person = null, $flush = true)
    {
        // Récupération des droits
        $credential = $this->resolveTimeSheetCredentials($timesheet, $person);

        if (!$credential['deletable'] == true) {
            throw new OscarException(sprintf("Impossible de supprimer le créneau %s du %s, seul un créneau non-soumis peut être supprimé.\n", $timesheet->getLabel(), $timesheet->getDateFrom()->format('Y-m-d')));
        } else {
            $this->getEntityManager()->remove($timesheet);
            if ($flush) {
                $this->getEntityManager()->flush($timesheet);
            }
        }
    }


    private $notificationsDatas;

    protected function stackNotification($message, Activity $activity, $action, array $persons)
    {
        if ($this->notificationsDatas === null) {
            $this->notificationsDatas = [];
        }
        $key = 'Activity:' . $action . ':' . $activity->getId();
        if (!array_key_exists($key, $this->notificationsDatas)) {
            $this->notificationsDatas[$key] = [
                'message' => $message,
                'action' => $action,
                'persons' => $persons,
                'activity' => $activity
            ];
        } else {
            $this->notificationsDatas[$key]['persons'] = array_unique(array_merge($this->notificationsDatas[$key]['persons'], $persons));
        }
    }

    protected function getStackedNotifications()
    {
        return $this->notificationsDatas;
    }

    /**
     * @return NotificationService
     */
    protected function getServiceNotification()
    {
        return $this->getServiceLocator()->get("NotificationService");
    }

    protected function sendStackedNotifications()
    {
        if ($this->notificationsDatas) {
            foreach ($this->notificationsDatas as $activityKey => $datas) {
                $this->getServiceNotification()->notification(
                    sprintf($datas['message'], $datas['activity']->log()),
                    $datas['persons'],
                    'Activity',
                    $datas['activity']->getId(),
                    $datas['action'],
                    new \DateTime(),
                    new \DateTime()
                );
            }
        }
    }

    public function validateSci($datas, $by)
    {
        $timesheets = [];

        $currentPersonName = "Oscar Bot";
        $currentPersonId = -1;

        if ($by) {
            $currentPersonName = (string)$by;
            $currentPersonId = $by->getId();
        }

        // Traitement
        foreach ($datas as $data) {
            if (array_key_exists('id', $data)) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);

                $timeSheet
                    ->setStatus($timeSheet->getValidatedAdminAt() ? TimeSheet::STATUS_ACTIVE : TimeSheet::STATUS_TOVALIDATE)
                    // On supprime les informations de rejet
                    ->setRejectedSciAt(null)
                    ->setRejectedSciBy(null)
                    ->setRejectedSciComment(null)
                    ->setValidatedSciAt(new \DateTime())
                    ->setValidatedSciBy($currentPersonName)
                    ->setValidatedSciById($currentPersonId);

                $this->getEntityManager()->flush($timeSheet);
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;

                $this->stackNotification(
                    sprintf("Des déclarations ont été validés scientifiquement dans l'activité %s", $timeSheet->getActivity()->log()),
                    $timeSheet->getActivity(),
                    'validatesci',
                    [$timeSheet->getPerson()]
                );

            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }

        $this->sendStackedNotifications();


        return $timesheets;
    }

    /**
     * Déclenchement de la validation administrative.
     *
     * @param $datas
     * @param $by
     * @return array
     */
    public function validateAdmin($datas, $by)
    {
        $timesheets = [];

        $currentPersonName = "Oscar Bot";
        $currentPersonId = -1;
        if ($by) {
            $currentPersonName = (string)$by;
            $currentPersonId = $by->getId();
        }

        // Traitement
        foreach ($datas as $data) {
            if (array_key_exists('id', $data)) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);

                $timeSheet
                    ->setStatus($timeSheet->getValidatedSciAt() ? TimeSheet::STATUS_ACTIVE : TimeSheet::STATUS_TOVALIDATE)
                    ->setValidatedAdminAt(new \DateTime())
                    ->setValidatedAdminBy($currentPersonName)
                    ->setValidatedAdminById($currentPersonId)
                    ->setRejectedAdminAt(null)
                    ->setRejectedAdminBy(null)
                    ->setRejectedAdminComment(null);

                $this->getEntityManager()->flush($timeSheet);
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;

                $this->stackNotification(
                    sprintf("Des déclarations ont été validées administrativement dans l'activité %s", $timeSheet->getActivity()->log()),
                    $timeSheet->getActivity(),
                    'validateadmin',
                    [$timeSheet->getPerson()]
                );

            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }
        $this->sendStackedNotifications();
        return $timesheets;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// OUTPUT
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @param Person $person
     * @param $from JOUR de début au format YYYY-MM-DD, ex: 2018-07-01
     * @param $to JOUR de fin au format YYYY-MM-DD, ex: 2018-07-31
     */
    public function getTimesheetsPersonPeriod(Person $person, $from, $to)
    {
        // Récupération des créneaux présents dans Oscar
        $query = $this->getEntityManager()->getRepository(TimeSheet::class)->createQueryBuilder('t');
        $query->where('t.dateFrom >= :start AND t.dateTo <= :end AND t.person = :person')
            ->orderBy('t.dateFrom')
            ->setParameters([
                // PATCH Aout 2018
                // Ajout des heures pour récupérer les créneaux du dernier jour
                // Note : DoctrineExtension ne semble pas fonctionner (usage de DATE(Champ))
                'start' => $from . ' 00:00:00',
                'end' => $to . ' 23:59:59',
                'person' => $person,
            ]);
        return $query->getQuery()->getResult();
    }
}
