<?php


namespace Oscar\Strategy\Upload;


class StrategyGedUpload implements StrategyTypeInterface
{

    private $document;

    public function __construct(TypeDocumentInterface $document)
    {
        $this->document= $document;
    }

    public function uploadDocument(TypeDocumentInterface $typeDocument): void
    {
        echo "je suis dans la stratégie GED UPLOAD ! method uploadDocument";
        // TODO: Implement uploadDocument() method.
    }

    public function getDocument(): TypeDocumentInterface
    {
        return $this->document;
    }
}