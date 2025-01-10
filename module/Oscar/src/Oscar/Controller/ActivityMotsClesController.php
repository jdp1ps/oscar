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
                return $this->getMotsCles();
            }

            if ($this->getRequest()->getMethod() == "POST") {
                $action_mot_cle_json = json_decode($this->getRequest()->getContent());

                if ($action_mot_cle_json->action == "create") {
                    return $this->createMotCle($action_mot_cle_json);
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

    private function getMotsCles() {

        /** @var ActivityMotCleRepository $activityMotCleRepository */
        $activityMotCleRepository = $this->getEntityManager()->getRepository(ActivityMotCle::class);

        $motsCles = [];
        foreach ($activityMotCleRepository->getAll() as $motCle) {
            $motsCles[] = [
                'id'    => $motCle->getId(),
                'label' => $motCle->getLabel(),
            ];
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

}
