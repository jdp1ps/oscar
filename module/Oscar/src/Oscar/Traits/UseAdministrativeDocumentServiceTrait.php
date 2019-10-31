<?php
namespace Oscar\Traits;

use Oscar\Service\AdministrativeDocumentService;

trait UseAdministrativeDocumentServiceTrait
{
    /**
     * @var AdministrativeDocumentService
     */
    private $administrativeDocumentService;

    /**
     * @param AdministrativeDocumentService $administrativeDocumentService
     */
    public function setAdministrativeDocumentService( AdministrativeDocumentService $administrativeDocumentService ) :void
    {
        $this->administrativeDocumentService = $administrativeDocumentService;
    }

    /**
     * @return AdministrativeDocumentService
     */
    public function getAdministrativeDocumentService() :AdministrativeDocumentService {
        return $this->administrativeDocumentService;
    }
}