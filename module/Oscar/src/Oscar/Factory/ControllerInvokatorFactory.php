<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 15:47
 */

namespace Oscar\Factory;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class ControllerInvokatorFactory implements AbstractFactoryInterface
{

    protected $pathController = __DIR__.'/../Controller/';

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $controllerFilePath = $this->pathController.$requestedName.'Controller.php';
        return file_exists($controllerFilePath);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $className = "Oscar\Controller\\" . $requestedName . "Controller";
        $controller = new $className;
        $controller->setServiceLocator($container);
        return $controller;
    }
}