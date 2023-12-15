<?php

namespace Oscar\Traits;

use Oscar\Service\DocumentFormatterService;

interface UseDocumentFormatterService
{
    public function setDocumentFormatterService(DocumentFormatterService $s): void;

    public function getDocumentFormatterService(): DocumentFormatterService;
}