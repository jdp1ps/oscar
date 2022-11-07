<?php


namespace Oscar\Strategy\Upload;


class StrategyGedUpload implements StrategyTypeInterface
{

    private $document;
    private $etat;

    public function __construct(){}

    public function uploadDocument(): void
    {
        echo "je suis dans la stratégie GED UPLOAD ! method uploadDocument";
        // TODO: Implement uploadDocument() method.
    }

    public function getDocument(): TypeDocumentInterface
    {
        return $this->document;
    }

    public function setDocument(TypeDocumentInterface $document): void
    {
        $this->document = $document;
    }

    public function getDatas(): array
    {
        // TODO: Implement getDatas() method.
        return [];
    }

    public function getEtat(): bool
    {
        // TODO: Implement getEtat() method.
        return $this->etat;
    }

    public function setEtat(bool $etat): void
    {
        // TODO: Implement setEtat() method.
        $this->etat = $etat;
    }
}
