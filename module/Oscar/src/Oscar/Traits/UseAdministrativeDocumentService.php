<?php
namespace Oscar\Traits;

use Oscar\Service\AdministrativeDocumentService;

interface UseAdministrativeDocumentService
{
    /**
     * @param AdministrativeDocumentService $administrativeDocumentService
     */
    public function setAdministrativeDocumentService( AdministrativeDocumentService $administrativeDocumentService ) :void;

    /**
     * @return AdministrativeDocumentService
     */
    public function getAdministrativeDocumentService() :AdministrativeDocumentService ;
}