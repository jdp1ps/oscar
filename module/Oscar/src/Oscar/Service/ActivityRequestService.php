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
use Oscar\Entity\ActivityRequest;
use Oscar\Entity\ActivityRequestFollow;
use Oscar\Entity\ActivityRequestRepository;
use Oscar\Entity\Notification;
use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class ActivityRequestService
 * @package Oscar\Service
 */
class ActivityRequestService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;


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
    public function getActivityRequestPerson(Person $person, $format = 'object')
    {
        try {
            $query = $this->getEntityManager()->getRepository(ActivityRequest::class)->createQueryBuilder('r')
                ->where('r.createdBy = :person')
                ->setParameter('person', $person)
                ->getQuery();

            if ($format == 'json') {
                $array = [];
                /** @var ActivityRequest $activityRequest */
                foreach ($query->getResult() as $activityRequest) {
                    $json = $activityRequest->toJson();
                    $json['statutText'] = $this->getStatutText($json['statut']);
                    $json['editable'] = $activityRequest->getStatus() == 1;
                    $json['sendable'] = $activityRequest->getStatus() == 1;
                    $array[] = $json;
                }

                return $array;
            }

            return $query->getResult();
        } catch (\Exception $e) {
            throw new OscarException("Impossible de charger les demande pour $person : " . $e->getMessage());
        }
    }

    public function getAllRequestActivityUnDraft( $organizationsFilter = false ){
        /** @var ActivityRequestRepository $requestActivityRepository */
        $requestActivityRepository = $this->getEntityManager()->getRepository(ActivityRequest::class);

        if( $organizationsFilter === false )
            return $requestActivityRepository->getAll();
        else {
            return $requestActivityRepository->getAllForOrganizations($organizationsFilter);
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

    public function valid( ActivityRequest $activityRequest, Person $validator ){
        if( $activityRequest->getStatus() != ActivityRequest::STATUS_SEND ){
            throw new OscarException("Conflit de status");
        }

        /** @var NotificationService $notificationService */
        $notificationService = $this->getServiceLocator()->get("NotificationService");

        /** @var ProjectGrantService $notificationService */
        $activityService = $this->getServiceLocator()->get("ActivityService");

        $activity = new Activity();
        $this->getEntityManager()->persist($activity);
        $activity->setLabel($activityRequest->getLabel())
            ->setDescription($activityRequest->getDescription())
            ->setAmount($activityRequest->getAmount());

        // todo : Ajout de la personne

        // todo : Ajout de l'oganisme

        $this->getEntityManager()->flush();

        // Mise à jour de l'index de recherche
        $activityService->searchUpdate($activity);


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




        throw new OscarException("Cette fonctionnalité n'est pas encore implantée");
    }

    public function reject( ActivityRequest $activityRequest ){
        throw new OscarException("Cette fonctionnalité n'est pas encore implantée");
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
        $this->getServiceLocator()->get('Logger')->debug("Suppression");
        $dir = $this->getServiceLocator()->get('OscarConfig')->getConfiguration('paths.document_request');

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
