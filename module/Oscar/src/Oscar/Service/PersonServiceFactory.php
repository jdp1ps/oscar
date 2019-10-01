<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:45
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Oscar\Traits\UseNotificationService;
use Zend\ServiceManager\Factory\FactoryInterface;

class PersonServiceFactory extends AbstractServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new PersonService();

        return $this->init($s, $container);
    }


}