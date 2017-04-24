<?php

namespace <namespace>;

use <targetFullClass>;
use RuntimeException;

/**
 * Description of <class>
 *
 * @author UnicaenCode
 */
trait <class>
{
    /**
     * @var <targetClass>
     */
    private $<variable>;





    /**
     * @param <targetClass> $<variable>
     * @return self
     */
    public function set<method>( <targetClass> $<variable> )
    {
        $this-><variable> = $<variable>;
        return $this;
    }



    /**
     * @return <targetClass>
     * @throws RuntimeException
     */
    public function get<method>()
    {
        if (empty($this-><variable>)){
        if (! method_exists($this, 'getServiceLocator')) {
            throw new RuntimeException( 'La classe '.get_class($this).' n\'a pas accès au ServiceLocator.');
        }

        $serviceLocator = $this->getServiceLocator();
        if (method_exists($serviceLocator, 'getServiceLocator')) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }
        $this-><variable> = $serviceLocator->get('HydratorManager')->get('<name>');
        }
        return $this-><variable>;
    }
}