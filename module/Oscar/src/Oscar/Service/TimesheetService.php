<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Connector\ConnectorPersonOrganization;
use Oscar\Entity\Activity;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\TimeSheet;
use Oscar\Entity\WorkPackage;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\Mapper\Ldap\People;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
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
     * @return OscarUserContext
     */
    protected function getOscarUserContext()
    {
        return $this->getServiceLocator()->get('OscarUserContext');
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
        foreach ($datas as $data) {
            if ($data['id']) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
                $timeSheet->setStatus(TimeSheet::STATUS_TOVALIDATE);
                $this->getEntityManager()->flush($timeSheet);
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;
            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }
        return $timesheets;
    }

    /**
     * Retourne la liste des déclaration de la personne.
     *
     * @param Person $person
     * @return array
     */
    function allByPerson(Person $person)
    {
        $timesheets = [];
        $datas = $this->getEntityManager()->getRepository(TimeSheet::class)->findBy(['person' => $person]);

        /** @var TimeSheet $data */
        foreach ($datas as $data) {
            $json = $data->toJson();
            $json['credentials'] = $this->resolveTimeSheetCredentials($data);
            $timesheets[] = $json;
        }

        return $timesheets;
    }

    public function allByActivity( Activity $activity ){
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

    /**
     * Résolution des droits sur une déclaration
     * @param TimeSheet $timeSheet
     * @return array
     */
    public function resolveTimeSheetCredentials(TimeSheet $timeSheet)
    {

        $deletable = false;

        // Le créneau ne peut être envoyé que par sont propriétaire et si
        // le status est "Bouillon"
        $sendable = $this->getOscarUserContext()->getCurrentPerson() == $timeSheet->getPerson()
            &&
            $timeSheet->getStatus() == TimeSheet::STATUS_DRAFT;

        $editable = false;

        // @deprecated
        $validable = false;

        $validableSci = $timeSheet->getValidatedSciAt() ?
            false :
            $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_SCI,
                $timeSheet->getActivity());

        $validableAdm = $timeSheet->getValidatedSciAt() ?
            false :
            $this->getOscarUserContext()->hasPrivileges(Privileges::ACTIVITY_TIMESHEET_VALIDATE_ADM,
                $timeSheet->getActivity());

        // En fonction du status
        switch ($timeSheet->getStatus()) {
            case TimeSheet::STATUS_DRAFT :
                $deletable = true;
                $editable = true;
                $validable = false;
                break;

            case TimeSheet::STATUS_INFO :
                $deletable = true;
                $editable = true;
                $validable = false;
                break;

            case TimeSheet::STATUS_CONFLICT :
                $deletable = true;
                $editable = true;
                $validable = false;
                break;

            case TimeSheet::STATUS_TOVALIDATE :
                $deletable = false;
                $editable = false;
                $validable = false;
                break;
        }

        return [
            'deletable' => $deletable,
            'editable' => $editable,
            'sendable' => $sendable,
            'validableSci' => $validableSci,
            'validableAdm' => $validableAdm
        ];
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
            if ($data['id'] && $data['id'] != 'null') {
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);
            } else {
                $timeSheet = new TimeSheet();
                $this->getEntityManager()->persist($timeSheet);
            }

            $status = TimeSheet::STATUS_INFO;

            if (isset($data['idworkpackage']) && $data['idworkpackage'] != 'null') {
                $workPackage = $this->getEntityManager()->getRepository(WorkPackage::class)->find($data['idworkpackage']);
                $timeSheet->setWorkpackage($workPackage);
                $status = TimeSheet::STATUS_DRAFT;
            } elseif (isset($data['idactivity']) && $data['idactivity'] != 'null') {
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($data['idactivity']);
                $timeSheet->setActivity($activity);
                $status = TimeSheet::STATUS_DRAFT;
            }

            $timeSheet->setComment($data['description'])
                ->setLabel($data['label'])
                ->setCreatedBy($by)
                ->setPerson($by)
                ->setStatus($status)
                ->setDateFrom(new \DateTime($data['start']))
                ->setDateTo(new \DateTime($data['end']));

            $json = $timeSheet->toJson();
            $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
            $timesheets[] = $json;

        }
        $this->getEntityManager()->flush($timeSheet);

        return $timesheets;
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
        $timesheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($timesheetId);
        if (!$timesheet) {
            throw new OscarException("Créneau introuvable.");
        }

        try {
            $this->getEntityManager()->remove($timesheet);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            throw new OscarException("BD Error : Impossible de supprimer le créneau.");
        }

        return true;
    }

    public function rejectSci( $datas, $by ){
        $timesheets = [];

        $currentPersonName = "Oscar Bot";
        $currentPersonId = -1;
        if( $by ){
            $currentPersonName = (string)$by;
            $currentPersonId = $by->getId();
        }

        // Traitement
        foreach ($datas as $data) {
            if ( array_key_exists('id', $data) ) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);

                $timeSheet
                    ->setStatus(TimeSheet::STATUS_CONFLICT)
                    ->setRejectedSciAt(new \DateTime())
                    ->setRejectedSciBy($currentPersonName)
                    ->setRejectedSciById($currentPersonId)
                    ->setRejectedSciComment($data['rejectedSciComment']);

                $this->getEntityManager()->flush($timeSheet);
/*
                switch( $action ){
                    case 'validatesci' :
                        $timeSheet
                            ->setStatus(TimeSheet::STATUS_ACTIVE)
                            ->setValidatedSciAt(new \DateTime())
                            ->setValidatedSciBy($currentPersonName)
                            ->setValidatedSciById($currentPersonId);
                        break;

                    case 'validateadmin' :
                        $timeSheet
                            ->setStatus(TimeSheet::STATUS_ACTIVE)
                            ->setValidatedSciAt(new \DateTime())
                            ->setValidatedSciBy($currentPersonName)
                            ->setValidatedSciById($currentPersonId);
                        break;

                    case 'send' :
                        $timeSheet
                            ->setStatus(TimeSheet::STATUS_TOVALIDATE)
                            ->setSendBy($currentPersonName);
                        break;

                    case 'rejectsci' :

                        break;

                    case 'rejectadmin' :
                        $timeSheet
                            ->setStatus(TimeSheet::STATUS_CONFLICT)
                            ->setRejectedSciAt(new \DateTime())
                            ->setRejectedSciBy($currentPersonName)
                            ->setRejectedSciById($currentPersonId)
                            ->setRejectedSciComment($data['rejectedAdminComment']);
                        break;
                }
                $this->getEntityManager()->flush($timeSheet);
*/
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;
                return $timesheets;
            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }
    }

    public function validateSci($datas, $by)
    {
        $timesheets = [];

        $currentPersonName = "Oscar Bot";
        $currentPersonId = -1;
        if( $by ){
            $currentPersonName = (string)$by;
            $currentPersonId = $by->getId();
        }

        // Traitement
        foreach ($datas as $data) {
            if ( array_key_exists('id', $data) ) {
                /** @var TimeSheet $timeSheet */
                $timeSheet = $this->getEntityManager()->getRepository(TimeSheet::class)->find($data['id']);

                $timeSheet
                    ->setStatus(TimeSheet::STATUS_ACTIVE)
                    ->setValidatedSciAt(new \DateTime())
                    ->setValidatedSciBy($currentPersonName)
                    ->setValidatedSciById($currentPersonId);

                $this->getEntityManager()->flush($timeSheet);
                /*
                                switch( $action ){
                                    case 'validatesci' :

                                        break;

                                    case 'validateadmin' :
                                        $timeSheet
                                            ->setStatus(TimeSheet::STATUS_ACTIVE)
                                            ->setValidatedSciAt(new \DateTime())
                                            ->setValidatedSciBy($currentPersonName)
                                            ->setValidatedSciById($currentPersonId);
                                        break;

                                    case 'send' :
                                        $timeSheet
                                            ->setStatus(TimeSheet::STATUS_TOVALIDATE)
                                            ->setSendBy($currentPersonName);
                                        break;

                                    case 'rejectsci' :

                                        break;

                                    case 'rejectadmin' :
                                        $timeSheet
                                            ->setStatus(TimeSheet::STATUS_CONFLICT)
                                            ->setRejectedSciAt(new \DateTime())
                                            ->setRejectedSciBy($currentPersonName)
                                            ->setRejectedSciById($currentPersonId)
                                            ->setRejectedSciComment($data['rejectedAdminComment']);
                                        break;
                                }
                                $this->getEntityManager()->flush($timeSheet);
                */
                $json = $timeSheet->toJson();
                $json['credentials'] = $this->resolveTimeSheetCredentials($timeSheet);
                $timesheets[] = $json;
                return $timesheets;
            } else {
                return $this->getResponseBadRequest("DOBEFORE");
            }
        }
    }

    public function rejectAdmin( $datas, $by ){

    }

    public function validateAdmin($datas, $by)
    {
        throw new OscarException('Validate Admin not implemented');
        var_dump('VA : ' . $data . ' - ' . (string)$by);
        die("Test");
    }

}
