<?php
namespace Oscar\Traits;


use Oscar\Service\TypeDocumentService;

interface UseTypeDocumentService
{
    /**
     * @param TypeDocumentService $typeDocumentService
     */
    public function setTypeDocumentService( TypeDocumentService $typeDocumentService ) :void;

    /**
     * @return TypeDocumentService
     */
    public function getTypeDocumentService() :TypeDocumentService ;
}