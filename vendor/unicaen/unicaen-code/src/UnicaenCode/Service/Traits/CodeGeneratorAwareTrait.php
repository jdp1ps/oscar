<?php

namespace UnicaenCode\Service\Traits;

use UnicaenCode\Service\CodeGenerator;
use Common\Exception\RuntimeException;
use UnicaenCode\Util;

trait CodeGeneratorAwareTrait
{
    /**
     * @var CodeGenerator
     */
    private $serviceCodeGenerator;

    /**
     *
     * @param CodeGenerator $serviceCodeGenerator
     * @return self
     */
    public function setServiceCodeGenerator( CodeGenerator $serviceCodeGenerator )
    {
        $this->serviceCodeGenerator = $serviceCodeGenerator;
        return $this;
    }

    /**
     *
     * @return CodeGenerator
     * @throws \Common\Exception\RuntimeException
     */
    public function getServiceCodeGenerator()
    {
        if (! $this->serviceCodeGenerator){
            $this->serviceCodeGenerator = Util::getServiceLocator()->get('UnicaenCode\CodeGenerator');
        }
        return $this->serviceCodeGenerator;
    }

}