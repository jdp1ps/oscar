<?php


namespace Oscar\Strategy\Upload;


use Oscar\Entity\Activity;
use Oscar\Service\ActivityLogService;
use Oscar\Service\NotificationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\VersionnedDocumentService;
use Zend\Http\Request;

class TypeGed implements TypeDocumentInterface
{

    private $typeStockage;

    public function __construct($typeStockage)
    {
        $this->typeStockage = $typeStockage;
    }

    public function returnStatut(): bool
    {
        // TODO: Implement returnStatut() method.
    }

    public function getTypeStockage(): string
    {
        // TODO: Implement getTypeStockage() method.
    }

    public function setTypeStockage($typeStockage): void
    {
        // TODO: Implement setTypeStockage() method.
    }

    public function getActivity(): Activity
    {
        // TODO: Implement getActivity() method.
    }

    public function setActivity(Activity $activity): void
    {
        // TODO: Implement setActivity() method.
    }

    public function getRequest(): Request
    {
        // TODO: Implement getRequest() method.
    }

    public function setRequest(Request $request): void
    {
        // TODO: Implement setRequest() method.
    }

    public function getDocumentService(): VersionnedDocumentService
    {
        // TODO: Implement getDocumentService() method.
    }

    public function setDocumentService(VersionnedDocumentService $documentService): void
    {
        // TODO: Implement setDocumentService() method.
    }

    public function getDocReplaced(): ?string
    {
        // TODO: Implement getDocReplaced() method.
    }

    public function setDocReplaced(?string $docReplaced): void
    {
        // TODO: Implement setDocReplaced() method.
    }

    public function getOscarUserContext(): OscarUserContext
    {
        // TODO: Implement getOscarUserContext() method.
    }

    public function setOscarUserContext(OscarUserContext $oscarUserContext): void
    {
        // TODO: Implement setOscarUserContext() method.
    }

    public function getNotificationService(): NotificationService
    {
        // TODO: Implement getNotificationService() method.
    }

    public function setNotificationService(NotificationService $notificationService): void
    {
        // TODO: Implement setNotificationService() method.
    }

    public function getActivityLogService(): ActivityLogService
    {
        // TODO: Implement getActivityLogService() method.
    }

    public function setActivityLogService(ActivityLogService $activityLogService): void
    {
        // TODO: Implement setActivityLogService() method.
    }

    public function init(
        $typeStockage,
        Activity $activity,
        Request $request,
        VersionnedDocumentService $documentService,
        ?string $docReplaced,
        OscarUserContext $oscarUserContext,
        NotificationService $notificationService,
        ActivityLogService $activityLogService
    ): void {
        // TODO: Implement init() method.
    }
}
