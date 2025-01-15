<?php

namespace Oscar\Controller;

use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Oscar\Entity\ActivityMotCle;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Throwable;

class ActivityMotsClesController extends AbstractOscarController implements UseLoggerService
{
    use UseLoggerServiceTrait;

    public function apiAction()
    {
        try {

            if ($this->getRequest()->getMethod() == "GET") {
                $includeActivityCountQueryParam = $this->params()->fromQuery('include_activity_count');
                $includeActivityCount = $includeActivityCountQueryParam && $includeActivityCountQueryParam === "true";

                return $this->getMotsCles($includeActivityCount);
            }

            if ($this->getRequest()->getMethod() == "POST") {
                $action_mot_cle_json = json_decode($this->getRequest()->getContent());

                if ($action_mot_cle_json->action == "create") {
                    return $this->createMotCle($action_mot_cle_json);
                }

                if ($action_mot_cle_json->action == "delete") {
                    return $this->deleteMotCle($action_mot_cle_json);
                }

                if ($action_mot_cle_json->action == "update") {
                    return $this->updateMotCle($action_mot_cle_json);
                }

                if ($action_mot_cle_json->action == "fusion") {
                    return $this->fusionnerMotsCles($action_mot_cle_json);
                }
            }

            $response = new Response();
            $response->setStatusCode(Response::STATUS_CODE_405);
            $response->setContent("Method Not Allowed.");
            return $response;

        } catch (Throwable $e) {
            $response = new Response();
            $response->setStatusCode(Response::STATUS_CODE_500);
            $response->setContent($e->getMessage());
            return $response;
        }
    }

    private function getMotsCles($includeCount) {

        /** @var ActivityMotCleRepository $activityMotCleRepository */
        $activityMotCleRepository = $this->getEntityManager()->getRepository(ActivityMotCle::class);

        $motsCles = [];

        $tousLesMotsCles = [];
        if ($includeCount) {
            foreach ($activityMotCleRepository->getMotsClesCounted() as $motCle) {
                $motsCles[] = [
                    'id'    => $motCle['id'],
                    'label' => $motCle['label'],
                    'activity_count' => $motCle['activity_count'],
                ];
            }
        } else {
            foreach ($activityMotCleRepository->getAll() as $motCle) {
                $motsCles[] = [
                    'id'    => $motCle->getId(),
                    'label' => $motCle->getLabel(),
                ];
            }
        }

        $response = new JsonModel();
        $response->setVariables(['motscles' => $motsCles]);
        return $response;
    }

    private function createMotCle($action_mot_cle_json) {

        $this->getLoggerService()->info("createMotCle");
        $this->getLoggerService()->info("by: " . $this->getCurrentPerson());
        $motCle = new ActivityMotCle();
        $motCle->setLabel($action_mot_cle_json->label);
        $motCle->setCreatedBy($this->getCurrentPerson());
        $this->getEntityManager()->persist($motCle);
        $this->getEntityManager()->flush();

        $response = new JsonModel();
        $response->setVariables(['id' => $motCle->getId(), 'label' => $motCle->getLabel()]);
        return $response;
    }

    private function deleteMotCle($action_mot_cle_json) {

        $this->getLoggerService()->info("deleteMotCle");
        $this->getLoggerService()->info("by: " . $this->getCurrentPerson());

        $motCle = $this->getEntityManager()
            ->getRepository(ActivityMotCle::class)
            ->findOneBy(array('id' => $action_mot_cle_json->id));
        if (!$motCle) {
            throw new \Exception("Le mot clé n'existe pas. Veuillez actualiser la page.");
        }

        $this->getEntityManager()->remove($motCle);
        $this->getEntityManager()->flush();

        $response = new JsonModel();
        $response->setVariables(['id' => $action_mot_cle_json->id]);
        return $response;
    }

    private function updateMotCle($action_mot_cle_json) {

        $this->getLoggerService()->info("updateMotCle");
        $this->getLoggerService()->info("by: " . $this->getCurrentPerson());

        $motCle = $this->getEntityManager()
            ->getRepository(ActivityMotCle::class)
            ->findOneBy(array('id' => $action_mot_cle_json->id));
        if (!$motCle) {
            throw new \Exception("Le mot clé n'existe pas. Veuillez actualiser la page.");
        }

        $motCle->setLabel($action_mot_cle_json->label);
        $this->getEntityManager()->persist($motCle);
        $this->getEntityManager()->flush();

        $response = new JsonModel();
        $response->setVariables(['id' => $motCle->getId(), 'label' => $motCle->getLabel()]);
        return $response;
    }

    private function fusionnerMotsCles($action_mot_cle_json) {

        $this->getLoggerService()->info("fusionMotCle");
        $this->getLoggerService()->info("by: " . $this->getCurrentPerson());

        $targetId = $action_mot_cle_json->targetId;
        if (!$targetId || !is_numeric($targetId)) {
            throw new \Exception("Le paramètre targetId doit être un identifiant numérique.");
        }
        $motCleCible = $this->getEntityManager()
            ->getRepository(ActivityMotCle::class)
            ->findOneBy(array('id' => $targetId));
        if (!$motCleCible) {
            throw new \Exception("Le mot clé cible n'existe pas. Veuillez actualiser la page.");
        }

        $idsToMerge = $action_mot_cle_json->idsToMerge;
        if (!$idsToMerge || !is_array($idsToMerge) || count($idsToMerge) < 2) {
            throw new \Exception("Le paramètre idsToMerge doit être un tableau d'au moins deux identifiants numériques de mots clés.");
        }

        $motsClesASupprimer = [];
        foreach ($idsToMerge as $idToMerge) {
            if (!is_numeric($idToMerge)) {
                throw new \Exception("Le paramètre idsToMerge doit contenir des identifiants numériques de mots clés.");
            }
            if ($idToMerge == $targetId) {
                continue;
            }
            $motCleASupprimer = $this->getEntityManager()
                ->getRepository(ActivityMotCle::class)
                ->findOneBy(array('id' => $idToMerge));
            if (!$motCleASupprimer) {
                throw new \Exception("Le mot clé à supprimer n'existe pas. Veuillez actualiser la page.");
            }
            $motsClesASupprimer[] = $motCleASupprimer;
        }

        foreach ($motsClesASupprimer as $motCleASupprimer) {
            foreach ($motCleASupprimer->getActivities() as $activity) {
                if (!$activity->hasMotcle($motCleCible)) {
                    $activity->addMotCle($motCleCible);
                    $this->getEntityManager()->persist($activity);
                    $this->getEntityManager()->flush();
                }
            }
        }

        foreach ($motsClesASupprimer as $motCleASupprimer) {
            $this->getEntityManager()->remove($motCleASupprimer);
            $this->getEntityManager()->flush();
        }

        $response = new JsonModel();
        $response->setVariables([]);
        return $response;
    }
}
