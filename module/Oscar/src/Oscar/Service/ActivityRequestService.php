<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 25/09/15 10:20
 *
 * @copyright Certic (c) 2015
 */

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityOrganization;
use Oscar\Entity\ActivityPerson;
use Oscar\Entity\ActivityRequest;
use Oscar\Entity\ActivityRequestFollow;
use Oscar\Entity\ActivityRequestRepository;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\ContractDocumentRepository;
use Oscar\Entity\Currency;
use Oscar\Entity\Notification;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseNotificationServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Oscar\Traits\UseProjectGrantService;
use Oscar\Traits\UseProjectGrantServiceTrait;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class ActivityRequestService
 * @package Oscar\Service
 */
class ActivityRequestService implements UseEntityManager, UsePersonService, UseOscarConfigurationService, UseNotificationService, UseProjectGrantService, UseLoggerService {

    use UseEntityManagerTrait, UsePersonServiceTrait, UseOscarConfigurationServiceTrait, UseNotificationServiceTrait, UseProjectGrantServiceTrait, UseLoggerServiceTrait;
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// RÉCUPÉRATION DES DONNÉES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * @param $id
     * @param bool $throw
     * @return null|ActivityRequest
     * @throws OscarException
     */
    public function getActivityRequest($id, $throw = true)
    {
        $activityRequest = $this->getEntityManager()->getRepository(ActivityRequest::class)->find($id);
        if (!$activityRequest && $throw == true) {
            throw new OscarException("Impossible de charger la demande N° $id");
        }
        return $activityRequest;
    }

    /**
     * Retourne la liste des demandes d'une personne.
     *
     * @param Person $person
     * @return array
     */
    public function getActivityRequestPerson(Person $person, $format = 'object', $status = [])
    {
        if( count($status) == 0 ) return [];
        try {
            /** @var ActivityRequestRepository $activityRequestRepository */
            $activityRequestRepository = $this->getEntityManager()->getRepository(ActivityRequest::class);

            $activityRequests = $activityRequestRepository->getAllForPerson($person, $status);

            if ($format == 'json') {
                $array = [];
                /** @var ActivityRequest $activityRequest */
                foreach ($activityRequests as $activityRequest) {
                    $json = $activityRequest->toJson();
                    $json['statutText'] = $this->getStatutText($json['statut']);
                    $json['editable'] = $activityRequest->getStatus() == 1;
                    $json['sendable'] = $activityRequest->getStatus() == 1;
                    $array[] = $json;
                }

                return $array;
            }

            return $activityRequests;
        } catch (\Exception $e) {
            throw new OscarException("Impossible de charger les demande pour $person : " . $e->getMessage());
        }
    }

    public function getAllRequestActivityUnDraft( $organizationsFilter = false ){
        /** @var ActivityRequestRepository $requestActivityRepository */
        $requestActivityRepository = $this->getEntityManager()->getRepository(ActivityRequest::class);

        if( $organizationsFilter === false )
            return $requestActivityRepository->getAll([ActivityRequest::STATUS_SEND]);
        else {
            return $requestActivityRepository->getAllForOrganizations($organizationsFilter, [ActivityRequest::STATUS_SEND]);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// ENREGISTREMENT DES DONNÉES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function createActivityRequest($datas)
    {
        throw new OscarException("La création des demandes d'activité n'est pas encore implanté.");
    }

    public function updateActivityRequest($datas)
    {
        throw new OscarException("L'enregistrement des demandes d'activité n'est pas encore implanté.");
    }

    /**
     * Validation d'une demande d'activité.
     *
     * @param ActivityRequest $activityRequest
     * @param Person $validator
     * @param null $personsDatas
     * @param null $organisationDatas
     * @return bool
     * @throws OscarException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function valid( ActivityRequest $activityRequest, Person $validator, $personsDatas = null, $organisationDatas = null ){

        // Test du status
        if( $activityRequest->getStatus() != ActivityRequest::STATUS_SEND ){
            throw new OscarException("Conflit de status");
        }

        /** @var NotificationService $notificationService */
        $notificationService = $this->getNotificationService();

        /** @var ProjectGrantService $notificationService */
        $activityService = $this->getProjectGrantService();

        $currency = $this->getEntityManager()->getRepository(Currency::class)->findOneBy([
            'label' => $this->getOscarConfigurationService()->getConfiguration('defaultCurrency')
        ]);

        $activity = new Activity();
        $this->getEntityManager()->persist($activity);
        $activity->setLabel($activityRequest->getLabel())
            ->setDescription($activityRequest->getDescription())
            ->setAmount($activityRequest->getAmount())
            ->setDateStart($activityRequest->getDateStart())
            ->setDateEnd($activityRequest->getDateEnd())
            ->setPcruPoleCompetitivite(null)
            ->setPcruValidPoleCompetitivite(false)
            ->setCurrency($currency);

        $person = $this->getPersonService()->getPersonById($activityRequest->getCreatedBy()->getId(), true);

        if( $personsDatas ){
            $rolePerson = $this->getPersonService()->getRolePersonById($personsDatas['roleid'], false);
            $activityPerson = new ActivityPerson();
            $this->getEntityManager()->persist($activityPerson);
            $activityPerson->setPerson($person)
                ->setActivity($activity)
                ->setRoleObj($rolePerson);
        }

        if( $organisationDatas && $activityRequest->getOrganisation() ){
            $roleOrganization = $this->getEntityManager()->getRepository(OrganizationRole::class)->find($organisationDatas['roleid']);
            $activityOrganization = new ActivityOrganization();
            $this->getEntityManager()->persist($activityOrganization);
            $activityOrganization->setOrganization($activityRequest->getOrganisation())
                ->setActivity($activity)
                ->setRoleObj($roleOrganization);
        }

        $dirSource = $this->getOscarConfigurationService()->getConfiguration('paths.document_request');
        $dirDest = $this->getOscarConfigurationService()->getConfiguration('paths.document_oscar');

        /** @var ContractDocumentRepository $documentRepo */
        $documentRepo = $this->getEntityManager()->getRepository(ContractDocument::class);
        $defaultTab = $documentRepo->getDefaultTabDocument();

        foreach ($activityRequest->getFilesArray() as $file) {
            $contractDocument = new ContractDocument();
            $this->getEntityManager()->persist($contractDocument);
            $contractDocument->setFileName($file['name'])
                ->setVersion(1)
                ->setGrant($activity)
                ->setFileSize($file['size'])
                ->setTabDocument($defaultTab)
                ->setPath($file['name'])
                ->setDateDeposit($activityRequest->getDateCreated())
                ->setDateUpdoad($activityRequest->getDateCreated())
                ->setDateSend($activityRequest->getDateCreated())
                ->setFileTypeMime($file['type']);

            $this->getEntityManager()->flush($contractDocument);

            $realName = $contractDocument->generatePath();
            $contractDocument->setPath($realName);

            $from = $dirSource.'/'.$file['file'];
            $to = $dirDest.'/'.$realName;


            // déplacement du fichier
            if( !rename($from, $to) ){
                $this->getLoggerService()->error("Impossibe de déplacer le fichier $from vers $to");
            }
            $contractDocument->setPath($realName);
        }

        $this->getEntityManager()->flush();

        // Mise à jour de l'index de recherche
        $activityService->getGearmanJobLauncherService()->triggerUpdateSearchIndexActivity($activity);

        // Ajout du Follow
        $follow = new ActivityRequestFollow();
        $this->getEntityManager()->persist($follow);

        $follow->setActivityRequest($activityRequest)
            ->setDescription("Demande validée")
            ->setDateCreated(new \DateTime())
            ->setCreatedBy($validator);

        $activityRequest->setStatus(ActivityRequest::STATUS_VALID);

        $this->getEntityManager()->flush();

        // todo Notification du demandeur
        $demandeur = $activityRequest->getCreatedBy();
        $notificationService->notification(
            sprintf("La demande %s a été validée par %s", $activityRequest->getLabel(), $validator),
            [$demandeur],
            Notification::OBJECT_ACTIVITY,
            $activity->getId(),
            Notification::OBJECT_ACTIVITY,
            new \DateTime(),
            new \DateTime()
        );

        return true;
    }

    public function reject( ActivityRequest $activityRequest, Person $validator ){
        // Test du status
        if( $activityRequest->getStatus() != ActivityRequest::STATUS_SEND ){
            throw new OscarException("Conflit de status");
        }

        /** @var NotificationService $notificationService */
        $notificationService = $this->getNotificationService();

        // TODO Suppression des fichiers envoyés
//        $dirSource = $this->>$this->getOscarConfigurationService()->getConfiguration('paths.document_request');
//
//        foreach ($activityRequest->getFilesArray() as $file) {
//            $contractDocument = new ContractDocument();
//            $this->getEntityManager()->persist($contractDocument);
//            $contractDocument->setFileName($file['name'])
//                ->setVersion(1)
//                ->setGrant($activity)
//                ->setFileSize($file['size'])
//                ->setPath($file['name'])
//                ->setDateDeposit($activityRequest->getDateCreated())
//                ->setDateUpdoad($activityRequest->getDateCreated())
//                ->setDateSend($activityRequest->getDateCreated())
//                ->setFileTypeMime($file['type']);
//
//            $this->getEntityManager()->flush($contractDocument);
//
//            $realName = $contractDocument->generatePath();
//            $contractDocument->setPath($realName);
//
//            $from = $dirSource.'/'.$file['file'];
//            $to = $dirDest.'/'.$realName;
//
//
//            // déplacement du fichier
//            if( !rename($from, $to) ){
//                $this->>getLoggerService()->error("Impossibe de déplacer le fichier $from vers $to");
//            }
//            $contractDocument->setPath($realName);
//        }


        // Ajout du Follow
        $follow = new ActivityRequestFollow();
        $this->getEntityManager()->persist($follow);

        $follow->setActivityRequest($activityRequest)
            ->setDescription("Demande rejetée")
            ->setDateCreated(new \DateTime())
            ->setCreatedBy($validator);

        $activityRequest->setStatus(ActivityRequest::STATUS_REJECT);

        $this->getEntityManager()->flush();

        // todo Notification du demandeur
        $demandeur = $activityRequest->getCreatedBy();
        $notificationService->notification(
            sprintf("La demande %s a été refusée par %s", $activityRequest->getLabel(), $validator),
            [$demandeur],
            Notification::OBJECT_ACTIVITY,
            -1,
            Notification::OBJECT_ACTIVITY,
            new \DateTime(),
            new \DateTime()
        );

        return true;
    }

    public function createOrUpdateActivityRequest($datas)
    {
        if ($datas['id']) {
            $this->updateActivityRequest($datas);
        } else {
            $this->createActivityRequest($datas);
        }
    }

    public function deleteActivityRequest( ActivityRequest $activityRequest ){
        if( $activityRequest->getStatus() != ActivityRequest::STATUS_DRAFT ){
            throw new OscarException("Vous ne pouvez pas supprimer cette demande");
        }


        // Suppression des fichiers
        $dir = $this->getOscarConfigurationService()->getConfiguration('paths.document_request');

        // Suppression des fichiers
        foreach ($activityRequest->getFilesArray() as $fileInfos){
            $filepath = $dir.'/'.$fileInfos['file'];
            if( file_exists($filepath) ){
                @unlink($filepath);
            }
        }

        $this->getEntityManager()->remove($activityRequest);
        $this->getEntityManager()->flush($activityRequest);
        return true;
    }

    public function sendActivityRequest(ActivityRequest $activityRequest, Person $sender)
    {
        if( $activityRequest->getStatus() != ActivityRequest::STATUS_DRAFT ){
            throw new OscarException("Cette demande a déjà été envoyée");
        }
        try {
            $follow = new ActivityRequestFollow();
            $this->getEntityManager()->persist($follow);

            $follow->setActivityRequest($activityRequest)
                ->setDescription("Demande envoyée")
                ->setDateCreated(new \DateTime())
                ->setCreatedBy($sender);

            $activityRequest->setStatus(ActivityRequest::STATUS_SEND);
            $this->getEntityManager()->flush();
        } catch (\Exception $err){
            throw new OscarException("Impossible d'envoyer la demande.");
        }
        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///
    /// TRAITEMENT DES DONNÉES
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function getStatutText( $statut ){
        static $statutText;
        if( !$statutText ){
            $statutText = [
                1 => "Brouillon",
                2 => "Envoyée",
            ];
        }
        if( array_key_exists($statut, $statutText) ){
            return $statutText[$statut];
        } else {
            return "Status inconnu";
        }
    }


}
