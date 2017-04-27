<?php

namespace UnicaenCode\Service\Traits;

use UnicaenCode\Service\Introspection;
use Common\Exception\RuntimeException;
use UnicaenCode\Util;

trait IntrospectionAwareTrait
{
    /**
     * @var Introspection
     */
    private $serviceIntrospection;

    /**
     *
     * @param Introspection $serviceIntrospection
     * @return self
     */
    public function setServiceIntrospection( Introspection $serviceIntrospection )
    {
        $this->serviceIntrospection = $serviceIntrospection;
        return $this;
    }

    /**
     *
     * @return Introspection
     * @throws \Common\Exception\RuntimeException
     */
    public function getServiceIntrospection()
    {
        if (! $this->serviceIntrospection){
            $this->serviceIntrospection = Util::getServiceLocator()->get('UnicaenCode\Introspection');
        }
        return $this->serviceIntrospection;
    }

}