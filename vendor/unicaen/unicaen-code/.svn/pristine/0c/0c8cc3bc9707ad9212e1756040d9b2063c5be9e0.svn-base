<?php

namespace UnicaenCode\Service\Traits;

use UnicaenCode\Service\Collector;
use Common\Exception\RuntimeException;
use UnicaenCode\Util;

trait CollectorAwareTrait
{
    /**
     * @var Collector
     */
    private $serviceCollector;

    /**
     *
     * @param Collector $serviceCollector
     * @return self
     */
    public function setServiceCollector( Collector $serviceCollector )
    {
        $this->serviceCollector = $serviceCollector;
        return $this;
    }

    /**
     *
     * @return Collector
     * @throws \Common\Exception\RuntimeException
     */
    public function getServiceCollector()
    {
        if (! $this->serviceCollector){
            $this->serviceCollector = Util::getServiceLocator()->get('UnicaenCode\Collector');
        }
        return $this->serviceCollector;
    }

}