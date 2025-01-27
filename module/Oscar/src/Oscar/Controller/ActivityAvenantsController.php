<?php

namespace Oscar\Controller;

use Oscar\Entity\Activity;
use Oscar\Service\ActivityAvenantsService;
use Oscar\Service\ProjectGrantApiService;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;

class ActivityAvenantsController extends AbstractOscarController implements UseLoggerService, UseOscarUserContextService
{
    use UseLoggerServiceTrait, UseOscarUserContextServiceTrait;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private ActivityAvenantsService $activityAvenantsService;

    public function getActivityAvenantsService(): ActivityAvenantsService
    {
        return $this->activityAvenantsService;
    }

    public function setActivityAvenantsService(ActivityAvenantsService $activityAvenantsService): self
    {
        $this->activityAvenantsService = $activityAvenantsService;
        return $this;
    }

    private ProjectGrantApiService $projectGrantApiService;

    public function getProjectGrantApiService(): ProjectGrantApiService
    {
        return $this->projectGrantApiService;
    }

    public function setProjectGrantApiService(ProjectGrantApiService $projectGrantApiService): self
    {
        $this->projectGrantApiService = $projectGrantApiService;
        return $this;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function apiAction()
    {
        $this->getLoggerService()->debug(__METHOD__);

        $idActivity = $this->params()->fromRoute('activity_id');
        try {
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
        } catch (\Exception $e) {
            return $this->jsonError("Impossible de charger l'activité $idActivity");
        }

        switch ($this->getHttpXMethod()) {
            case 'GET':
                try {
                    $datas = $this->getProjectGrantApiService()->getActivityJson(
                        $activity->getId(),
                        $this->url(),
                        $this->getOscarUserContextService(),
                        ProjectGrantApiService::PERIMETER_AVENANTS
                    );
                    return $this->jsonOutput($datas);
                } catch (\Exception $e) {
                    return $this->jsonError("Impossible de charger les avenants");
                }
                break;

            case 'POST':
                // TODO Tester les droits d'accès
                $datas = $this->getJsonREST();
                try {
                    $this->getActivityAvenantsService()->createAvenantFromArray($activity, $datas);
                    return $this->getResponseOk("Avenant ajouté");
                } catch (\Exception $e) {
                    return $this->jsonError("Impossible d'ajouter l'avenant : " . $e->getMessage());
                }

            case 'DELETE':
                // TODO Tester les droits d'accès
                try {
                    $id = $this->params()->fromRoute("avenant_id");
                    $this->getActivityAvenantsService()->deleteAvenantById($id);
                    return $this->getResponseOk("Avenant supprimé");
                } catch (\Exception $e) {
                    return $this->jsonError("Impossible de supprimer l'avenant : " . $e->getMessage());
                }

            default:
                return $this->getResponseBadRequest();
        }
    }
}
