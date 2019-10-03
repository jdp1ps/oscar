<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 03/10/19
 * Time: 15:24
 */

namespace Oscar\Traits;


use Psr\Container\ContainerInterface;

trait UseServiceContainerTrait
{
    /** @var ContainerInterface */
    private $serviceContainer;
    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function setServiceContainer( ContainerInterface $container) {
        $this->serviceContainer = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer() :ContainerInterface {
        return $this->serviceContainer;
    }
}