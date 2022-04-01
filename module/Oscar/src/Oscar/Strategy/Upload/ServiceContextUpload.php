<?php


namespace Oscar\Strategy\Upload;


use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use DoctrineORMModule\Options\EntityManager;
use DoctrineORMModule\Service\EntityManagerAliasCompatFactory;
use Oscar\Constantes\Constantes;
use Oscar\Controller\ContractDocumentController;
use Oscar\Entity\Activity;
use Oscar\Entity\ContractDocument;
use Oscar\Service\ActivityLogService;
use Oscar\Service\NotificationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\VersionnedDocumentService;
use Oscar\Traits\UseActivityLogServiceTrait;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Zend\Http\Request;
use Zend\Mvc\Controller\Plugin\Redirect;

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
        ActivityLogService $activityLogService

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
    }

    /**
     * @return bool
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function processUpload():bool
    {
        // Choix stratégie et hydrater l'objet Type choisi avec la stratégie
        // GED ou OSCAR (POST OU PAS POST)
        $isPost = false;
        // Mise à jour document id doc
        if ($this->docId) {
            $doc = new ContractDocument();
            if ($doc = $this->documentService->getDocument($this->docId)->getQuery()->getSingleResult()) {
                $this->docReplaced = $doc->getFileName();
            }
        }
        // Exemple ci-dessous avec constantes pour faire des traitements de tests nous partons sur GED OSCAR FILE INTERNE AVEC TEST SUR LES POSTS
        if( $this->request->isPost() ) {
            $isPost = true;
            // Ci-dessous juste pour tester avec Ged Oscar Actuellement
            // $typeDocumentStrategyConfig = Constantes::GED_UP;
            $typeDocumentStrategyConfig = Constantes::GED_OSCAR;
            // Fin bla bla
            $typeDocumentGedOscar = new TypeOscar
            (
                $typeDocumentStrategyConfig,
                $this->activity,
                $this->request,
                $this->documentService,
                $this->docReplaced,
                $this->oscarUserContext,
                $this->notificationService,
                $this->activityLogService
            );
            // Ici il serait bon de récup la stratégie choisis via un fichier de config
            $this->strategy = new StrategyOscarUpload($typeDocumentGedOscar);
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
        //Nous ne savons pas quelle stratégie est utilisée (design pattern stratégie plus ou moins) mais c'est l'objectif
        //$this->strategy = $strategy;
        //$this->strategy->uploadDocument();
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
