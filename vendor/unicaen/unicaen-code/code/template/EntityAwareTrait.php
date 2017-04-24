<?php

namespace <namespace>;

use <targetFullClass>;

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
    public function set<method>( <targetClass> $<variable> = null )
    {
        $this-><variable> = $<variable>;
        return $this;
    }



    /**
     * @return <targetClass>
     */
    public function get<method>()
    {
        return $this-><variable>;
    }
}