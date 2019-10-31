<?php
namespace Oscar\Traits;

use Oscar\Service\TypeDocumentService;

trait UseTypeDocumentServiceTrait
{
    /**
     * @var TypeDocumentService
     */
    private $typeDocumentService;

    /**
     * @param TypeDocumentService $typeDocumentService
     */
    public function setTypeDocumentService( TypeDocumentService $typeDocumentService ) :void
    {
        $this->typeDocumentService = $typeDocumentService;
    }

    /**
     * @return TypeDocumentService
     */
    public function getTypeDocumentService() :TypeDocumentService {
        return $this->typeDocumentService;
    }
}