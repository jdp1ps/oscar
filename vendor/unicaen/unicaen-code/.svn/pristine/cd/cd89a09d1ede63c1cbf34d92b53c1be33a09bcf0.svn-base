<?php

namespace UnicaenCode\Service\Traits;

use UnicaenCode\Service\Config;
use Common\Exception\RuntimeException;
use UnicaenCode\Util;

trait ConfigAwareTrait
{
    /**
     * @var Config
     */
    private $serviceConfig;

    /**
     *
     * @param Config $serviceConfig
     * @return self
     */
    public function setServiceConfig( Config $serviceConfig )
    {
        $this->serviceConfig = $serviceConfig;
        return $this;
    }

    /**
     *
     * @return Config
     * @throws \Common\Exception\RuntimeException
     */
    public function getServiceConfig()
    {
        if (! $this->serviceConfig){
            $this->serviceConfig = Util::getServiceLocator()->get('UnicaenCode\Config');
        }
        return $this->serviceConfig;
    }

}