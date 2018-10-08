<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\Authentification;
use Oscar\Entity\Organization;
use Oscar\Entity\Person;
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
     * @return ConfigurationParser
     */
    protected function getOscarConfig()
    {
        return $this->getServiceLocator()->get('OscarConfig');
    }

    //////////////////////////////////////////////////////////////////////// VALIDATION des PERIODES
    public function validationProject(ValidationPeriod $validationPeriod, Person $validator, $message = '')
    {
        if ($validationPeriod->getStatus() !== ValidationPeriod::STATUS_STEP1) {
            throw new OscarException("Erreur d'état");
        }

        $log = $validationPeriod->getLog();
        $person = (string)$validator;
        $date = new \DateTime();
        $msg = $date->format('Y-m-d H:i:s') . " : Validation PROJET par $person\n";
        $log .= $msg;
        $this->getLogger()->debug($msg);

        $validationPeriod->setLog($log);
        $validationPeriod->setStatus(ValidationPeriod::STATUS_STEP2);
        $validationPeriod->setValidationActivityAt($date)
            ->setValidationActivityBy((string)$validator)
            ->setValidationActivityById($validator->getId())
            ->setValidationActivityMessage($message);

        $this->getEntityManager()->flush($validationPeriod);

        return true;
    }

    public function validationSci(ValidationPeriod $validationPeriod, Person $validator, $message = '')
    {
        if ($validationPeriod->getStatus() !== ValidationPeriod::STATUS_STEP2) {
            throw new OscarException("Erreur d'état");
        }

        $log = $validationPeriod->getLog();
        $person = (string)$validator;
        $date = new \DateTime();
        $msg = $date->format('Y-m-d H:i:s') . " : Validation SCIENTIFIQUE par $person\n";
        $log .= $msg;
        $this->getLogger()->debug($msg);

        $validationPeriod->setLog($log);
        $validationPeriod->setStatus(ValidationPeriod::STATUS_STEP3);
        $validationPeriod->setValidationSciAt($date)
            ->setValidationSciBy((string)$validator)
            ->setValidationSciById($validator->getId())
            ->setValidationSciMessage($message);

        $this->getEntityManager()->flush($validationPeriod);

        return true;
    }


    public function validationAdm(ValidationPeriod $validationPeriod, Person $validator, $message = '')
    {
        $this->getLogger()->debug($validationPeriod->getObjectGroup() . " == " . ValidationPeriod::GROUP_OTHER);
        if ($validationPeriod->getObjectGroup() == ValidationPeriod::GROUP_OTHER) {
            if (!in_array($validationPeriod->getStatus(), [ValidationPeriod::STATUS_STEP1, ValidationPeriod::STATUS_STEP1, ValidationPeriod::STATUS_STEP3])) {
                throw new OscarException("Vous ne pouvez pas valider cette période (erreur de status - " . $validationPeriod->getStatus() . ").");
            }
        } else if ($validationPeriod->getStatus() !== ValidationPeriod::STATUS_STEP3) {
            throw new OscarException("Erreur d'état, la période doit être validée scientifiquement avant.");
        }

        $log = $validationPeriod->getLog();
        $person = (string)$validator;
        $date = new \DateTime();
        $msg = $date->format('Y-m-d H:i:s') . " : Validation ADMINISTRATIVE par $person\n";
        $log .= $msg;
        $this->getLogger()->debug($msg);

        $validationPeriod->setLog($log);
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
        $this->getLogger()->debug($msg);

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
        $this->getLogger()->debug($msg);

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
        $this->getLogger()->debug($validationPeriod->getObjectGroup() . " == " . ValidationPeriod::GROUP_OTHER);
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
        $this->getLogger()->debug($msg);

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
        $this->getLogger()->debug(sprintf('Récupération des validation pour %s', $activity));

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
        $this->getLogger()->debug(sprintf('Récupération des validation pour %s au %s-%s', $activity, $year, $month));
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


    public function getDeclarationConfigurationPerson(Person $person)
    {
        $conf = new ConfigurationMergable($this->getOscarConfig()->getConfiguration('declarationsDurations'));

    }

    /**
     * @param $idPeriod
     * @return null|ValidationPeriod
     */
    public function getValidationPeriod($idPeriod)
    {
        return $this->getValidationPeriodRepository()->find($idPeriod);
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
        $timesheets = [];

        $this->getLogger()->debug("Récupération des créneaux pour la période " . $validationPeriod);


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
    public function getDayDuration(Person $person)
    {
        return $this->getOscarConfig()->getConfiguration('declarationsDurations.dayLength.value');
    }

    public function getDayMaxLengthPerson(Person $person)
    {
        return $this->getOscarConfig()->getConfiguration('declarationsDurations.dayLength.max');
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

    public function getDaysPeriodInfosPerson(Person $person, $year, $month)
    {

        $dateRef = new \DateTime(sprintf('%s-%s-01', $year, $month));
        $locked = $this->getLockedDays($year, $month);

        // Nombre de jours dans le mois
        $nbr = cal_days_in_month(CAL_GREGORIAN, (int)$dateRef->format('m'), (int)$dateRef->format(('Y')));

        $daysFull = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];
        $decaleDay = $dateRef->format('N') - 1;
        $daysLabels = [];
        $weekendAllowed = $this->getOscarConfig()->getConfiguration('declarationsWeekend') == false;

        for ($i = $decaleDay; $i < $nbr + $decaleDay; $i++) {
            $duration = $this->getDayDuration($person);
            $maxlength = $this->getDayMaxLengthPerson($person);
            $day = $i - $decaleDay + 1;
            $dayIndex = ($i % 7);
            $close = false;
            $infos = "";
            $dayKey = $day < 10 ? '0' . $day : $day;
            $lockedKey = "$year-$month-$day";

            if (array_key_exists($lockedKey, $locked)) {
                $duration = 0.0;
                $maxlength = 0.0;
                $close = true;
                $infos = "Ferié " . $locked[$lockedKey];
            }

            if ($dayIndex > 4 && $weekendAllowed == true) {
                $duration = 0.0;
                $maxlength = 0.0;
                $close = true;
                $infos .= $infos ? " / Weekend" : "Weekend";
            }

//            $daysLabels[$dayKey] =  $daysFull[$dayIndex];
            $days[$dayKey] = [
                'duration' => 0.0,
                'dayLength' => $duration,
                'maxLength' => $maxlength,
                'label' => $daysFull[$dayIndex],
                'close' => $close,
                'infos' => $infos,
                'datefull' => $lockedKey,
            ];
        }

        return $days;
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

    public function getPersonPeriods(Person $person)
    {
        // Périodes
        $periods = $this->getValidationPeriodRepository()->getValidationPeriodsPersonQuery($person->getId())->getQuery()->getResult();
        $out = [];

        /** @var ValidationPeriod $period */
        foreach ($periods as $period) {

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

            $key = $timesheet->getDateFrom()->format('Y-m');
            $keyDay = $timesheet->getDateFrom()->format('Y-m-d');

            if (!array_key_exists($key, $out)) {
                $out[$key] = [
                    'periodCode' => $key,
                    'hasValidation' => false,
                    'days' => []
                ];
            }

            if (!array_key_exists($keyDay, $out[$key]['days'])) {
                $out[$key]['days'][$keyDay] = 0.0;
            }

            $out[$key]['days'][$keyDay] += $timesheet->getDuration();


        }

        return $out;
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
        $daysInfos = $this->getDaysPeriodInfosPerson($person, $year, $month);
        $totalDays = count($daysInfos);
        $periodLastDay = new \DateTime($period . '-' . $totalDays . ' 23:59:59');

        $periodInfos = "";
        $periodFutur = $periodFirstDay > $now;
        $periodFinished = $periodLastDay < $now;
        $periodCurrent = $period == $now->format('Y-m');

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
        $periodOpened = 0.0;
        $periodDeclarations = 0.0;


        /** @var ValidationPeriod $periodValidation */
        foreach ($periodValidations as $periodValidation) {
            $data = $periodValidation->json();

            if( $periodValidation->getObjectId() > 0 ){
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($periodValidation->getObjectId());
                $label = (string) $activity;
                $data['label'] = "Déclaration pour " . $label;
            }
            $periodValidationsDt[] = $data;
        }

        // Période pas encore commencée
        if( $periodFutur ){
            $periodInfos = "Ce mois n'a pas encore commencé";
        }

        if( $periodFinished ){
            $periodInfos = "Ce mois est terminé";

            // Il y'a des déclarations
            if( count($periodValidations) ){
                $submitable = false;
                $submitableInfos = "Vous avez déja envoyé cette période pour validation";
                $editable = false;
                $editableInfos  = "Vous avez déja envoyé cette période pour validation";
            } else {
                $submitable = true;
                $submitableInfos = "Ce mois est terminé, complétez votre déclaration avant de la soumettre";
                $editable = true;
                $editableInfos  = "Ce mois est terminé, complétez votre déclaration avant de la soumettre";
            }
        }

        if( $periodCurrent ){
            $periodInfos = "Mois en cours";
            $submitable = false;
            $submitableInfos = "mois en cours, vous ne pouvez soumettre vos déclarations qu'à la fin du mois";
            $editable = true;
            $editableInfos  = "Mois en cours, vous pouvez commencer à compléter votre déclaration";
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //// Récupération des lots de travail pour cette période
        $activities = [];
        $workPackages = [];

        $availableWorkPackages = $this->getServiceLocator()->get('ActivityService')->getWorkPackagePersonPeriod($person, $year, $month);
        /** @var WorkPackagePerson $workPackagePerson */
        foreach ($availableWorkPackages as $workPackagePerson){
            $workPackage = $workPackagePerson->getWorkPackage();
            $activity = $workPackage->getActivity();

            /** @var ValidationPeriod $period */
            $periodActivityValidation = $this->getValidationPeriodActivityAt($workPackage->getActivity(), $person, $year, $month);

            if( !array_key_exists($activity->getId()) ){
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

            if( $isPeriodSend ){
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

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// Récupération des créneaux Hors-Lot
        $others = $this->getOthersWP();
        $horsLot = [];

        foreach ($others as $key=>&$datas) {
            $periodHL = $this->getValidationPeriosOutOfWorkpackageAt($person, $year, $month, $key);

            if( $isPeriodSend ){
                $validationUp = $periodHL && $periodHL->isOpenForDeclaration();
            } else {
                $validationUp = true;
            }

            $horsLot[$key]['validation_state'] = $periodHL ? $periodHL->json() : null;
            $horsLot[$key]['validation_up'] = $validationUp;
        }



        foreach ($daysInfos as $day => $daydata) {
            $datetime = new \DateTime($daydata['datefull']);
            $locked = true;

            $editable = $editable;

            $daydata['date'] = $daydata['datefull'];
            $daydata['data'] = $daydata['datefull'];
            $daydata['day'] = $datetime->format('N');
            $daydata['week'] = $datetime->format('W');
            $daydata['editable'] = $editable;
            $daydata['declarations'] = [];
            $daydata['total'] = 0.0;

            /*foreach ($others as $otherKey=>$other) {
                $daydata[$otherKey] = [];
            }*/

            $output['days'][$day] = $daydata;
            //var_dump($daydata); die();
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $timesheets = $this->getTimesheetsPersonPeriod($person, $from, $to);
        /** @var TimeSheet $t */
        foreach ($timesheets as $t) {

            $dayTimesheet = (int)($t->getDateFrom()->format('d'));
            $period = $t->getDateFrom()->format('Y-m');


            if (!$t->getActivity()) {
                $periodKey = ValidationPeriod::GROUP_OTHER;


                $datas = [
                    'id' => $t->getId(),
                    'label' => $t->getLabel(),
                    'description' => $t->getComment(),
                    'duration' => $t->getDuration(),
                    'status_id' => $t->getStatus(),
                    'status' => 'locked',
                    'validations' => null,
                ];

                /*

                $period = $this->getValidationPeriosOutOfWorkpackageAt( $t->getPerson(), $year, $month, $t->getLabel());
                if( $period ){
                    if( $period->getStatus() == ValidationPeriod::STATUS_VALID ){
                        $datas['status_id'] = TimeSheet::STATUS_ACTIVE;
                        $datas['status_id'] = TimeSheet::STATUS_ACTIVE;
                        $datas['validations'] = $period->json();
                    }
                    elseif ($period->getStatus() != ValidationPeriod::STATUS_CONFLICT) {
                        $datas['status_id'] = TimeSheet::STATUS_TOVALIDATE_ADMIN;
                        $datas['status_id'] = TimeSheet::STATUS_TOVALIDATE_ADMIN;
                        $datas['validations'] = $period->json();
                    }
                    else {
                        $datas['status_id'] = TimeSheet::STATUS_CONFLICT;
                        $datas['status_id'] = TimeSheet::STATUS_CONFLICT;
                        $datas['validations'] = $period->json();
                    }
                }/****/

                $labelTimesheet = $t->getLabel();

                $otherRegular = false;

                /*$output['days'][$dayTimesheet]['infos'] = [];

                foreach ($others as $otherKey => $other) {

                    if( !array_key_exists($otherKey, $output['days'][$dayTimesheet]) ){
                        $output['days'][$dayTimesheet][$otherKey] = [];
                    }
                    if( $labelTimesheet == $otherKey ){
                        $output['days'][$dayTimesheet][$otherKey][] = $datas;
                        $otherRegular = true;
                    }
                }

                if( $otherRegular === false ){
                    $output['days'][$dayTimesheet]['infos'][] = $datas;
                }*/

               $daysInfos[$dayTimesheet]['othersWP'][] = $datas;
                $daysInfos[$dayTimesheet]['duration'] += (float)$t->getDuration();
                $output['total'] += (float)$t->getDuration();


                continue;
            } else {
                $periodKey = "activity-" . $t->getActivity()->getId();
            }

            $projectAcronym = $t->getActivity()->getAcronym();
            $project = $t->getActivity()->getProject();
            $activity = $t->getActivity();
            $activityCode = $activity->getOscarNum();
            $workpackage = $t->getWorkpackage();
            $wpCode = $workpackage->getCode();
            $periodDeclarations += (float)$t->getDuration();

            $output['days'][$dayTimesheet]['duration'] += (float)$t->getDuration();
            $periodLength += (float)$t->getDuration();
            $activities[$activity->getId()]['total'] += $t->getDuration();
            $workPackages[$workpackage->getId()]['total'] += $t->getDuration();


            if( $t->getStatus() == TimeSheet::STATUS_DRAFT ){
                $output['hasUnsend'] = true;
            }

            $daysInfos[$dayTimesheet]['declarations'][] = [
                'id' => $t->getId(),
                // 'credentials' => $this->resolveTimeSheetCredentials($t),
                // 'validations' => $this->resolveTimeSheetValidation($t),
                'label' => $t->getLabel(),
                'comment' => $t->getComment(),
                'activity' => (string) $activity,
                'activity_code' => $activityCode,
                'acronym' => $projectAcronym,
                'project' => (string)$project,
                'status_id' => $t->getStatus(),
                'status' => 'locked',
                'wpCode' => $wpCode,
                'duration' => (float)$t->getDuration(),
                'wp_id' => $t->getWorkpackage()->getId(),
            ];
        }

        $output = [
            'person' => (string)$person,
            'person_id' => $person->getId(),
            'period' => $periodFirstDay->format('Y-m'),
            'periodInfos' => $periodInfos,
            'periodFutur' => $periodFutur,
            'periodFinished' => $periodFinished,
            'periodCurrent' => $periodCurrent,
            'periodLength' => $periodLength,
            'periodOpened' => $periodOpened,
            'periodDeclarations' => $periodDeclarations,
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
            'daylength' => $this->getOscarConfig()->getConfiguration('declarationsDurations.dayLength.value'),
            'dayLength' => $this->getOscarConfig()->getConfiguration('declarationsDurations.dayLength.value'),
            'dayExcess' => $this->getOscarConfig()->getConfiguration('declarationsDurations.dayLength.max'),
            'weekExcess' => $this->getOscarConfig()->getConfiguration('declarationsDurations.weekLength.max'),
            'monthExcess' => $this->getOscarConfig()->getConfiguration('declarationsDurations.monthLength.max'),
            'activities' => $activities,
            'workpackages' => $workPackages,
            'otherWP' => $others,
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

            if ($periodFilter !== null && $periodFilter != $period)
                continue;

            if (!array_key_exists($activityId, $datas)) {
                $datas[$activityId] = [
                    'activityObj' => $timesheet->getActivity(),
                    'activity' => (string)$timesheet->getActivity(),
                    'project' => (string)$timesheet->getActivity()->getProject(),
                    'activity_id' => $timesheet->getActivity()->getId()
                ];
            }

            if (!array_key_exists($period, $datas[$activityId])) {
                $datas[$activityId][$period] = [
                    'unvalidate' => false,
                    'total' => 0.0,
                ];
                /** @var WorkPackage $wp */
                foreach ($timesheet->getActivity()->getWorkPackages() as $wp) {
                    if (!array_key_exists($wp->getCode(), $datas[$activityId]['timesheets'][$period]))
                        $datas[$activityId]['timesheets'][$period][$wp->getCode()] = [
                            'total' => 0.0
                        ];
                }
            }
            $datas[$activityId]['timesheets'][$period][$timesheet->getWorkpackage()->getCode()]['total'] += $timesheet->getDuration();
            $datas[$activityId]['timesheets'][$period]['total'] += $timesheet->getDuration();
            if ($timesheet->getStatus() != TimeSheet::STATUS_ACTIVE) {
                $datas[$activityId]['timesheets'][$period]['unvalidate'] = true;
            }

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
        $this->getLogger()->debug('ID:' . print_r($activitiesIdsWithValidationProgress, true));


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

        $this->getLogger()->debug($query->getSQL());

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
        $this->getLogger()->debug("Traitement pour $sender ($fromMonth - $toMonth)");

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
        $this->getLogger()->debug("Déclarations trouvées = " . count($declarations));

        if (count($declarations) > 0) {
            throw new OscarException("Vous avez déjà envoyé des déclarations pour cette période");
        }

        $declarations = [];

        $this->getLogger()->debug("Traitments des " . count($timesheets) . ' créneaux.');

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
                    'log' => "Déclaration envoyée",
                ];
                $this->createDeclaration($sender, $annee, $mois, $object, $objectId, $objectGroup);
            }

            //$this->setTimesheetToSend($timesheet);
        }

        $this->getEntityManager()->flush($timesheets);
        $this->getLogger()->debug(print_r($declarations, true));
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

        $now = new \DateTime();
        $declaration = new ValidationPeriod();
        $declaration->setStatus(ValidationPeriod::STATUS_STEP1)
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
        $this->getEntityManager()->flush($declaration);
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
        $this->getLogger()->debug(sprintf('Récupération des créneaux entre %s et %s pour %s', $from, $to
            , $person));
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
