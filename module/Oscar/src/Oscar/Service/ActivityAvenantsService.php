<?php

namespace Oscar\Service;

use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Moment\Moment;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityAvenant;
use Oscar\Entity\ActivityAvenantModification;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\Exception\OscarException;
use Oscar\Strategy\Upload\FileUploadStandard;
use Oscar\Traits\UseActivityLogService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
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
     * @param Activity $activity
     * @param array $datas
     * @return void
     * @throws NotSupported|ORMException|OscarException
     */
    public function saveAvenantFromArray(Activity $activity, array $datas): void
    {
        if ($datas['id']) {
            $mode = "update";
            $avenant = $this->getEntityManager()
                ->getRepository(ActivityAvenant::class)
                ->find($datas['id']);
            if( $avenant->getStatus() != ActivityAvenant::STATUS_DRAFT ){
                throw new OscarException("Avenant appliqué (non modifiable)");
            }
        }
        else {
            $mode = "create";
            $avenant = new ActivityAvenant();
            $avenant->setStatus(ActivityAvenant::STATUS_DRAFT);
            $avenant->setActivity($activity);
            $this->getEntityManager()->persist($avenant);
        }

        // Traitement des données
        try {
            $avenant_date = DateTimeUtils::getDateTimeFromStr($datas['dateAvenant']);
        } catch (\Exception $e) {
            throw new OscarException("Date de l'avenant invalide");
        }

        $idsModification = [];
        foreach ($avenant->getModifications() as $modification) {
            $idsModification[] = $modification->getId();
        }

        if ($datas['modifications']) {
            $modificationsSend = json_decode($datas['modifications'], true);
            foreach ($modificationsSend as $modification) {
                $idsModification = array_diff($idsModification, [$modification['id']]);
                $this->saveModification($avenant, $modification);
            }
        }

        $fileUploaded = $this->uploadAvenantFile($activity);
        if (($mode == "update" && $fileUploaded)) {
            $this->deleteAvenantFile($avenant->getFilename());
        }
        if ($fileUploaded) {
            $avenant->setFilename($fileUploaded);
        }

        if (($mode == "create" && !$fileUploaded)) {
            throw new OscarException("Fichier manquant");
        }

        // Modification
        foreach ($idsModification as $modificationId) {
            $modification = $this->getEntityManager()->getRepository(ActivityAvenantModification::class)->find($modificationId);
            $this->getEntityManager()->remove($modification);
        }

        try {
            $avenant->setDateAvenant($avenant_date);
            $avenant->setComment($datas['comment']);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            $this->getLoggerService()->critical($e->getMessage());
            if ($fileUploaded) {
                $this->deleteAvenantFile($fileUploaded);
            }
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

    public function applyAvenantById(mixed $id): void
    {
        try {
            $avenant = $this->getEntityManager()->getRepository(ActivityAvenant::class)->find($id);
            if( !$avenant ) {
                throw new OscarException("Impossible de trouver l'avenant");
            }

            if( $avenant->getStatus() != ActivityAvenant::STATUS_DRAFT) {
                throw new OscarException("Avenant déjà appliqué");
            }

            $activity = $avenant->getActivity();


            /** @var ActivityAvenantModification $modification */
            foreach ($avenant->getModifications() as $modification) {
                switch ($modification->getType()) {
                    case ActivityAvenantModification::TYPE_DATE_END:
                        $this->getLoggerService()->info($modification->getNewValue1());
                        $modification->setOldValue1($activity->getDateEndStr());
                        $activity->setDateEnd($modification->getNewValue1());
                        break;
                    default:
                        throw new OscarException("Type de modification '".$modification->getType()."' non-traité");
                }
            }
            $avenant->setStatus(ActivityAvenant::STATUS_ACTIVE);
            $activity->setLocked(true);
            $this->getEntityManager()->flush();

        } catch (\Exception $e) {
            $this->getLoggerService()->critical($e->getMessage());
            throw new OscarException("Impossible d'appliquer l'avenant '$id' : " . $e->getMessage());
        }
    }

    /**
     * Procédure de téléversement du fichier d'avenant.
     *
     * @param Activity $activity
     * @return string|null
     * @throws OscarException
     */
    private function uploadAvenantFile(Activity $activity): ?string
    {
        if (!array_key_exists('file', $_FILES)) {
            $this->getLoggerService()->error("Aucun fichier d'avenant envoyé");
            return null;
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
        } catch (\Exception $e) {
            $this->getLoggerService()->error($e->getMessage());
            throw new OscarException("Impossible de téléverser le fichier de l'avenant : " . $e->getMessage());
        }
    }

    /**
     * Suppression du fichier d'avenant.
     *
     * @param string $filename
     * @return bool
     */
    private function deleteAvenantFile(string $filename): bool
    {
        $this->getLoggerService()->debug("suppression du fichier '$filename'");
        try {
            $location = $this->getFileLocation($filename);
            if (!unlink($location)) {
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
    public function getFileInfos(ActivityAvenant $avenant): array
    {
        $location = $this->getFileLocation($avenant->getFilename());

        if (!file_exists($location)) {
            $this->getLoggerService()->critical("Fichier d'avenant manquant '$location'");
            throw new OscarException("Le fichier n'existe pas");
        }

        $size = filesize($location);
        $filename = $avenant->getFilename();

        // Déplacer le type dans la BDD
        $type = 'application/pdf';
        $content = FileSystemUtils::getInstance()->file_get_contents($location);

        $this->getLoggerService()->debug("Fichier : $location");
        $this->getLoggerService()->debug("Size : $size");
        $this->getLoggerService()->debug("Filename : $filename");
        $this->getLoggerService()->debug("Type : $type");
        $this->getLoggerService()->debug("Content : $content");

        return [
            'typemime' => $type,
            'path'     => $location,
            'filename' => $filename,
            'filesize' => $size,
            'content'  => $content
        ];
    }

    /**
     * @param string $filename
     * @return string
     * @throws OscarException
     */
    private function getFileLocation(string $filename): string
    {
        try {
            return $this->getOscarConfigurationService()->getDocumentDropLocation()
                . DIRECTORY_SEPARATOR
                . $filename;
        } catch (\Exception $e) {
            $msg = "Problème avec l'emplacement des avenants : " . $e->getMessage();
            $this->getLoggerService()->error($msg);
            throw new OscarException($msg);
        }
    }

    /**
     * Enregistrement d'un avenant.
     *
     * @param ActivityAvenant $avenant
     * @param array $modification
     * @param bool $fetch
     * @return void
     * @throws NotSupported
     * @throws ORMException
     * @throws OscarException
     */
    private function saveModification(ActivityAvenant $avenant, array $modification, bool $fetch = false): void
    {
        $this->getLoggerService()->debug("Traitement modification");
        $this->getLoggerService()->debug(json_encode($modification));

        $type = $modification['type'];
        $newValue1 = $modification['value1'];
        $newValue2 = $modification['value2'];
        $oldValue1 = null;
        $oldValue2 = null;

        if( !array_key_exists($type, ActivityAvenantModification::getTypes()) ) {
            throw new OscarException("Le type d'avenant '$type' n'existe pas");
        }

        if ($modification['id']) {
            $avenantModif = $this->getEntityManager()->getRepository(ActivityAvenantModification::class)->find(
                $modification['id']
            );
        }
        else {
            $avenantModif = new ActivityAvenantModification();
            $this->getEntityManager()->persist($avenantModif);
            $avenantModif->setAvenant($avenant);
        }

        $msg = "Modification non-traitée";
        switch ($type) {
            case ActivityAvenantModification::TYPE_DATE_END:
                $moment = Moment::fromDateTime(new \DateTime($newValue1));
                $msg = "Modification de la date de fin pour " . $moment->format('d/m/Y');
                $oldValue1 = $avenant->getActivity()->getDateEndStr();
                break;
            case ActivityAvenantModification::TYPE_PERSON_DEL:
                $person = $this->getEntityManager()->getRepository(Person::class)->find($newValue1);
                $role = $this->getEntityManager()->getRepository(Role::class)->find($newValue2);
                $msg = "Suppression de $person ($role)";
                break;
            case ActivityAvenantModification::TYPE_PERSON_ADD:
                $person = $this->getEntityManager()->getRepository(Person::class)->find($newValue1);
                $role = $this->getEntityManager()->getRepository(Role::class)->find($newValue2);
                $msg = "Ajout de $person ($role)";
                break;
            case ActivityAvenantModification::TYPE_ORGANIZATION_ADD:
                $organization = $this->getEntityManager()->getRepository(Organization::class)->find($newValue1);
                $role = $this->getEntityManager()->getRepository(OrganizationRole::class)->find($newValue2);
                $msg = "Ajout de $organization ($role)";
                break;
            case ActivityAvenantModification::TYPE_ORGANIZATION_DEL:
                $person = $this->getEntityManager()->getRepository(Organization::class)->find($newValue1);
                $role = $this->getEntityManager()->getRepository(OrganizationRole::class)->find($newValue2);
                $msg = "Suppression de $person ($role)";
                break;
            case ActivityAvenantModification::TYPE_CHANGE_AMOUNT:
                $oldValue1 = $avenant->getActivity()->getAmount();
                $msg = "Modification du montant à $newValue1 (avant : $oldValue1)";
                break;
        }

        $avenantModif->setType($modification['type']);
        $avenantModif->setNewValue1($newValue1);
        $avenantModif->setNewValue2($newValue2);
        $avenantModif->setOldValue1($oldValue1);
        $avenantModif->setOldValue2($oldValue2);
        $avenantModif->setInfo($msg);

        $this->getLoggerService()->debug("MODIF : " . $modification['type']);
    }
}