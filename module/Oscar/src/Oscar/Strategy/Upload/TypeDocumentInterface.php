<?php


namespace Oscar\Strategy\Upload;


use Oscar\Entity\Activity;
use Oscar\Service\ActivityLogService;
use Oscar\Service\NotificationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\VersionnedDocumentService;
use Zend\Http\Request;

interface TypeDocumentInterface
{
    public function returnStatut(): bool;
    public function getTypeStockage(): string;
    public function setTypeStockage($typeStockage): void;
    public function getActivity(): Activity;
    public function setActivity(Activity $activity): void;
    public function getRequest(): Request;
    public function setRequest(Request $request): void;
    public function getDocumentService(): VersionnedDocumentService;
    public function setDocumentService(VersionnedDocumentService $documentService): void;
    public function getDocReplaced(): ?String;
    public function setDocReplaced(?String $docReplaced): void;
    public function getOscarUserContext(): OscarUserContext;
    public function setOscarUserContext(OscarUserContext $oscarUserContext): void;
    public function getNotificationService(): NotificationService;
    public function setNotificationService(NotificationService $notificationService): void;
    public function getActivityLogService(): ActivityLogService;
    public function setActivityLogService(ActivityLogService $activityLogService): void;
}