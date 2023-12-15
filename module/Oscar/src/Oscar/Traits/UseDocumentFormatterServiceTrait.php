<?php

namespace Oscar\Traits;

use Oscar\Service\DocumentFormatterService;

trait UseDocumentFormatterServiceTrait
{

    private DocumentFormatterService $documentFormatterService;

    public function setDocumentFormatterService(DocumentFormatterService $documentFormatterService): void
    {
        $this->documentFormatterService = $documentFormatterService;
    }

    public function getDocumentFormatterService(): DocumentFormatterService
    {
        return $this->documentFormatterService;
    }
}