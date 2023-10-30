<?php

namespace UnicaenSignature\Controller;

use Psr\Container\ContainerInterface;

class SignatureControllerFactory {

    public function __invoke(ContainerInterface $container) : IndexController
    {
        $controller = new IndexController();
        return $controller;
    }

}