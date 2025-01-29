<?php

namespace Oscar\Controller;

use Oscar\Entity\Activity;
use Oscar\Entity\ActivityAvenant;
use Oscar\Exception\OscarException;
use Oscar\Service\ActivityAvenantsService;
use Oscar\Service\ProjectGrantApiService;
use Oscar\Strategy\Upload\FileUploadStandard;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Oscar\Utils\FileSystemUtils;

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

        try {
            $activity = $this->getActivityFromRoute();
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage());
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
                $datas = $_POST;
                try {
                    $datas['file'] = $this->fileAvenantDrop($activity);
                    $datas['status'] = ActivityAvenant::STATUS_DRAFT;
                    $this->getLoggerService()->debug(print_r($datas, true));
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

    public function downloadAction()
    {
        $this->getLoggerService()->debug(__METHOD__);


        $idAvenant = $this->params()->fromRoute('avenant_id');
        try {
            $avenant = $this->getEntityManager()->getRepository(ActivityAvenant::class)->find($idAvenant);
        } catch (\Exception $e) {
            throw new OscarException("Impossible de charger l'avenant $idAvenant");
        }

        // TODO check privileges
        $activity = $avenant->getActivity();

        try {
            $file_infos = $this->getActivityAvenantsService()->getFileInfos($avenant);
            $this->getLoggerService()->debug(
                "download file (avenant $idAvenant => "
                .$file_infos['path']
                ." --- "
                .$file_infos['typemime']
                .")");
            header('Content-Type: ' . $file_infos['typemime']);
            header('Content-Transfer-Encoding: Binary');
            header('Content-Disposition: attachment; filename="' . $file_infos['filename']);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $file_infos['filesize']);
            die($file_infos['content']);
        } catch (\Exception $e) {
            throw new OscarException("Impossible de télécharger l'avenant $idAvenant");
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @throws OscarException
     */
    protected function getActivityFromRoute( string $paramName = 'activity_id' ) : Activity
    {
        $idActivity = $this->params()->fromRoute($paramName);
        try {
            return $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
        } catch (\Exception $e) {
            throw new OscarException("Impossible de charger l'activité $idActivity");
        }
    }

    private function fileAvenantDrop( Activity $activity ) :string
    {
        if( !array_key_exists('file', $_FILES) ){
            $this->getLoggerService()->error("Aucun fichier d'avenant envoyé");
            throw new OscarException("Fichier manquant");
        }
        try {
            $uploader = new FileUploadStandard();
            $avenant_directory = $this->getOscarConfigurationService()->getDocumentDropLocation();
            $filename_pattern = $this->getOscarConfigurationService()->getConfiguration('avenant_filename');
            $filename = sprintf(
                $filename_pattern,
                $activity->getId(),
                (new \DateTime())->format('Y-m-d'),
                uniqid()
            );
            $mimes = ["application/pdf" => "pdf"];
            $uploader->setDestination($avenant_directory)
                ->setFilename($filename)
                ->setMimesAllowed($mimes);
            $uploader->updoad($_FILES['file']);
            $this->getLoggerService()->info("Upload ok");
            return $uploader->getUploadName();
        } catch (\Exception $e){
            $this->getLoggerService()->error($e->getMessage());
            throw new OscarException("Impossible de téléverser le fichier de l'avenant : " . $e->getMessage());
        }
    }
}
