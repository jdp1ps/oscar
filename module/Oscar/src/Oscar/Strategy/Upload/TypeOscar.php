<?php


namespace Oscar\Strategy\Upload;

use Oscar\Entity\Activity;
use Oscar\Service\ActivityLogService;
use Oscar\Service\NotificationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\VersionnedDocumentService;
use Zend\Http\Request;

class TypeOscar implements TypeDocumentInterface
{

    private $typeStockage;
    private $activity;
    private $request;
    private $documentService;
    private $docReplaced;
    private $oscarUserContext;
    private $notificationService;
    private $activityLogService;

    public function __construct
    (
        $typeStockage,
        Activity $activity,
        Request $request,
        VersionnedDocumentService $documentService,
        ?String $docReplaced,
        OscarUserContext $oscarUserContext,
        NotificationService $notificationService,
        ActivityLogService $activityLogService
    )
    {
        $this->typeStockage                     = $typeStockage;
        $this->activity                         = $activity;
        $this->request                          = $request;
        $this->documentService                  = $documentService;
        $this->docReplaced                      = $docReplaced;
        $this->oscarUserContext                 = $oscarUserContext;
        $this->notificationService              = $notificationService;
        $this->activityLogService               = $activityLogService;
    }

    public function returnStatut(): bool
    {
        // TODO: Implement returnStatut() method.
        return false;
    }

    /**
     * @return mixed
     */
    public function getTypeStockage(): string
    {
        return $this->typeStockage;
    }

    /**
     * @param mixed $typeStockage
     */
    public function setTypeStockage($typeStockage): void
    {
        $this->typeStockage = $typeStockage;
    }

    /**
     * @return Activity
     */
    public function getActivity(): Activity
    {
        return $this->activity;
    }

    /**
     * @param Activity $activity
     */
    public function setActivity(Activity $activity): void
    {
        $this->activity = $activity;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @return VersionnedDocumentService
     */
    public function getDocumentService(): VersionnedDocumentService
    {
        return $this->documentService;
    }

    /**
     * @param VersionnedDocumentService $documentService
     */
    public function setDocumentService(VersionnedDocumentService $documentService): void
    {
        $this->documentService = $documentService;
    }

    /**
     * @return String
     */
    public function getDocReplaced(): ?String
    {
        return $this->docReplaced;
    }

    /**
     * @param String $docReplaced
     */
    public function setDocReplaced(?String $docReplaced): void
    {
        $this->docReplaced = $docReplaced;
    }

    /**
     * @return OscarUserContext
     */
    public function getOscarUserContext(): OscarUserContext
    {
        return $this->oscarUserContext;
    }

    /**
     * @param OscarUserContext $oscarUserContext
     */
    public function setOscarUserContext(OscarUserContext $oscarUserContext): void
    {
        $this->oscarUserContext = $oscarUserContext;
    }

    /**
     * @return NotificationService
     */
    public function getNotificationService(): NotificationService
    {
        return $this->notificationService;
    }

    /**
     * @param NotificationService $notificationService
     */
    public function setNotificationService(NotificationService $notificationService): void
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @return ActivityLogService
     */
    public function getActivityLogService(): ActivityLogService
    {
        return $this->activityLogService;
    }

    /**
     * @param ActivityLogService $activityLogService
     */
    public function setActivityLogService(ActivityLogService $activityLogService): void
    {
        $this->activityLogService = $activityLogService;
    }
}
