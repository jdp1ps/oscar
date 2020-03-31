<?php


namespace Oscar\Strategy\Upload;


interface StrategyTypeInterface
{

    public function uploadDocument(): void;

    public function getDocument(): TypeDocumentInterface;

    public function getDatas(): array;

    public function getEtat(): int;

    public function setEtat(int $etat): void;
}