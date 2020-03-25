<?php


namespace Oscar\Strategy\Upload;


class TypeGed implements TypeDocumentInterface
{

    private $typeStockage;

    public function __construct($typeStockage)
    {
        $this->typeStockage = $typeStockage;
    }

    public function returnStatut(): bool
    {
        // TODO: Implement returnStatut() method.
    }
}