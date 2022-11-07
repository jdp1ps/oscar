<?php


namespace Oscar\Strategy\Upload;


interface StrategyTypeInterface
{

    public function uploadDocument(): void;

    public function setDocument(TypeDocumentInterface $document): void;

    public function getDocument(): TypeDocumentInterface;

    public function getDatas(): array;

    public function getEtat(): bool;

    public function setEtat(bool $etat): void;
}
