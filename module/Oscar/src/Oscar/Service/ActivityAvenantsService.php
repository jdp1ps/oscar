<?php

namespace Oscar\Service;

use Oscar\Entity\Activity;
use Oscar\Entity\ActivityAvenant;
use Oscar\Entity\ActivityPayment;
use Oscar\Entity\Currency;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Repository\ActivityPaymentRepository;
use Oscar\Exception\OscarException;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseNotificationService;
use Oscar\Traits\UseNotificationServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseServiceContainerTrait;
use Oscar\Utils\DateTimeUtils;
use Oscar\Utils\FileSystemUtils;

class ActivityAvenantsService implements
    UseLoggerService,
    UseEntityManager,
    UseActivityLogService,
    UseOscarConfigurationService
{

    use UseLoggerServiceTrait,
        UseEntityManagerTrait,
        UseActivityLogServiceTrait,
        UseOscarConfigurationServiceTrait;

    /**
     * Création d'un nouvel avenant.
     *
     * @param Activity $activity
     * @param array $datas
     * @return void
     * @throws OscarException
     */
    public function createAvenantFromArray(Activity $activity, array $datas): void
    {
        $this->getLoggerService()->debug(__METHOD__);

        // Traitement des données
        try {
            $avenant_date = DateTimeUtils::getDateTimeFromStr($datas['dateAvenant']);
        } catch (\Exception $e) {
            $this->deleteAvenantFile($datas['file']);
            throw new OscarException("Date de l'avenant invalide");
        }

        try {
            $avenant = new ActivityAvenant();
            $this->getEntityManager()->persist($avenant);
            $avenant->setDateAvenant($avenant_date);
            $avenant->setActivity($activity);
            $avenant->setComment($datas['comment']);
            $avenant->setFilename($datas['file']);
            $avenant->setStatus($datas['status']);
            $this->getEntityManager()->flush($avenant);
        } catch (\Exception $e) {
            $this->getLoggerService()->critical($e->getMessage());
            $this->deleteAvenantFile($datas['file']);
            throw new OscarException("Impossible de créer l'avenant");
        }
    }

    /**
     * @param mixed $id
     * @return void
     * @throws OscarException
     */
    public function deleteAvenantById(mixed $id): void
    {
        $this->getLoggerService()->debug(__METHOD__);
        try {
            $avenant = $this->getEntityManager()->getRepository(ActivityAvenant::class)->find($id);
            $this->deleteAvenantFile($avenant->getFilename());
            $this->getEntityManager()->remove($avenant);
            $this->getEntityManager()->flush();

        } catch (\Exception $e) {
            $this->getLoggerService()->critical($e->getMessage());
            throw new OscarException("Impossible de supprimer l'avenant");
        }
    }

    /**
     * @param string $filename
     * @return bool
     */
    private function deleteAvenantFile( string $filename ):bool
    {
        $this->getLoggerService()->debug("suppression du fichier '$filename'");
        try {
            $location = $this->getFileLocation($filename);
            if(!unlink($location)) {
                $this->getLoggerService()->error("Fichier '$location' non supprimé");
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @throws OscarException
     */
    public function getFileInfos(ActivityAvenant $avenant) :array
    {
        $location = $this->getFileLocation($avenant->getFilename());

        if( !file_exists($location) ){
            $this->getLoggerService()->critical("Fichier d'avenant manquant '$location'");
            throw new OscarException("Le fichier n'existe pas");
        }

        $size = filesize($location);
        $filename = $avenant->getFilename();
        $type = 'application/pdf';
        $content = FileSystemUtils::getInstance()->file_get_contents($location);

        $this->getLoggerService()->debug("Fichier : $location");
        $this->getLoggerService()->debug("Size : $size");
        $this->getLoggerService()->debug("Filename : $filename");
        $this->getLoggerService()->debug("Type : $type");
        $this->getLoggerService()->debug("Content : $content");

        return [
            'typemime' => $type,
            'path' => $location,
            'filename' => $filename,
            'filesize' => $size,
            'content' => $content
        ];
    }

    /**
     * @param string $filename
     * @return string
     * @throws OscarException
     */
    private function getFileLocation( string $filename ):string
    {
        try {
            return $this->getOscarConfigurationService()->getDocumentDropLocation()
                . DIRECTORY_SEPARATOR
                . $filename;
        } catch (\Exception $e){
            $msg = "Problème avec l'emplacement des avenants : " . $e->getMessage();
            $this->getLoggerService()->error($msg);
            throw new OscarException($msg);
        }
    }
}