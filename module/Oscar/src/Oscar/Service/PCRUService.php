<?php


namespace Oscar\Service;


use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseLoggerService;
use Oscar\Traits\UseLoggerServiceTrait;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;

class PCRUService implements UseLoggerService, UseOscarConfigurationService, UseEntityManager
{
    use UseEntityManagerTrait, UseOscarConfigurationServiceTrait, UseLoggerServiceTrait;

    private $pcruDepotStrategy;


    protected function getPCRUDepotStrategy(){
        if( $this->pcruDepotStrategy === null ){
            // Récupération de la configuration PCRU dans la configuration Oscar


        }
    }


}

