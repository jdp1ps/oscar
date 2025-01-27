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

    public function createAvenantFromArray(Activity $activity, array $datas): void
    {
        $this->getLoggerService()->debug(__METHOD__);

        // Traitement des données
        try {
            $avenant_date = DateTimeUtils::getDateTimeFromStr($datas['dateAvenant']);
        } catch (\Exception $e) {
            throw new OscarException("Date de l'avenant invalide");
        }
        $avenant_comment = $datas['comment'];
        $avenant_file = $datas['file'];

        if (strpos($avenant_file, '%PDF') !== 0) {
            throw new OscarException("Format de fichier invalide, PDF attendu");
        }

        // Traitement du fichier
        $avenant_directory = $this->getOscarConfigurationService()->getDocumentDropLocation();
        $filename_pattern = $this->getOscarConfigurationService()->getConfiguration('avenant_filename');
        $filename = sprintf(
            $filename_pattern,
            $activity->getId(),
            $avenant_date->format('Y-m-d'),
            uniqid()
        );
        $destination = $avenant_directory . DIRECTORY_SEPARATOR . $filename;
        $this->getLoggerService()->debug("envoi du fichier $filename");
        if (!file_put_contents($destination, $avenant_file)) {
            throw new OscarException("Impossible de traiter le fichier");
        }

        try {
            $avenant = new ActivityAvenant();
            $this->getEntityManager()->persist($avenant);
            $avenant->setDateAvenant($avenant_date);
            $avenant->setActivity($activity);
            $avenant->setComment($avenant_comment);
            $avenant->setFilename($filename);
            $this->getEntityManager()->flush($avenant);
        } catch (\Exception $e) {
            $this->getLoggerService()->critical($e->getMessage());
            throw new OscarException("Impossible de créer l'avenant");
        }
    }

    public function deleteAvenantById(mixed $id)
    {
        $this->getLoggerService()->debug(__METHOD__);
        throw new OscarException("A Faire");
    }
}