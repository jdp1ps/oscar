<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 03/10/19
 * Time: 15:22
 */

namespace Oscar\Traits;


use Psr\Container\ContainerInterface;

interface UseServiceContainer
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function setServiceContainer( ContainerInterface $container);

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer() :ContainerInterface ;
}