<?php


namespace Oscar\Strategy\Upload;


use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Constantes\Constantes;
use Oscar\Entity\Activity;
use Oscar\Entity\ContractDocument;
use Oscar\Service\ActivityLogService;
use Oscar\Service\NotificationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\VersionnedDocumentService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Laminas\Http\Request;

class ServiceContextUpload
{
    use UseOscarConfigurationServiceTrait, UseOscarUserContextServiceTrait, UseLoggerServiceTrait, UseEntityManagerTrait, UseActivityLogServiceTrait;

    private $strategy;
    private $request;
    private $docId;
    private $documentService;
    private $datas;
    private $idActivity;
    private $docReplaced = null;
    private $activity;
    private $oscarUserContext;
    private $notificationService;
    private $activityLogService;
    private $oscarConfigurationService;

    public function __construct
    (
        Request $request,
        $docId,
        VersionnedDocumentService $documentService,
        array $datas,
        $idActivity,
        Activity $activity,
        OscarUserContext $oscarUserContext,
        NotificationService $notificationService,
        ActivityLogService $activityLogService,
        OscarConfigurationService $oscarConfigurationService
    )
    {
        $this->request = $request;
        $this->docId = $docId;
        $this->documentService = $documentService;
        $this->datas = $datas;
        $this->idActivity = $idActivity;
        $this->activity = $activity;
        $this->oscarUserContext = $oscarUserContext;
        $this->notificationService = $notificationService;
        $this->activityLogService = $activityLogService;
        $this->oscarConfigurationService = $oscarConfigurationService;
    }

    /**
     * Méthode d'instanciation et traitement de téléversement via la stratégie choisis via fichier de configuration
     *
     * @return bool
     * @throws NoResultException
     * @throws NonUniqueResultException|\Oscar\Exception\OscarException
     */
    public function processUpload():bool
    {
        // GED X ou Y ou OSCAR (POST OU PAS POST)
        $isPost = false;
        // Mise à jour du document si id doc est fournis (mise à jour nouvelle version doc)
        if ($this->docId) {
            $doc = new ContractDocument();
            if ($doc = $this->documentService->getDocument($this->docId)->getQuery()->getSingleResult()) {
                $this->docReplaced = $doc->getFileName();
            }
        }
        if( $this->request->isPost() ) {
            $isPost = true;
            // Récup config pour init
            $gedConfig = $this->oscarConfigurationService->getConfiguration("strategyUpload");
            $typeDocumentConfig = $gedConfig["gedName"];
            $nameClassStrategy = $gedConfig["class"];
            $typeDocument = $gedConfig["typeStockage"];
            $typeDocumentGed = new $typeDocument;
            $typeDocumentGed->init
            (
                $typeDocumentConfig,
                $this->activity,
                $this->request,
                $this->documentService,
                $this->docReplaced,
                $this->oscarUserContext,
                $this->notificationService,
                $this->activityLogService
            );

            // Utilisations Params des fichiers de config oscar
            $this->strategy = new $nameClassStrategy;
            $this->strategy->setDocument($typeDocumentGed);
            $this->strategy->setEtat(1);
            $this->upload($this->strategy);
        }
        return $isPost;
    }

    /**
     * @param StrategyTypeInterface $strategy
     * @return void
     */
    private function upload(StrategyTypeInterface $strategy):void
    {
        $strategy->uploadDocument();
    }

    /**
     * @return mixed
     */
    public function getStrategy() : StrategyTypeInterface
    {
        return $this->strategy;
    }

    /**
     * @param mixed $strategy
     */
    public function setStrategy(StrategyTypeInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

}
