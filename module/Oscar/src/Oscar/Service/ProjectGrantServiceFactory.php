<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 19/09/19
 * Time: 18:45
 */

namespace Oscar\Service;


use Interop\Container\ContainerInterface;
use Oscar\Factory\AbstractOscarFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenSignature\Service\SignatureService;

class ProjectGrantServiceFactory extends AbstractOscarFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ProjectGrantService
    {
        $s = new ProjectGrantService();
        $this->init($s, $container);
        $s->setOrganizationService($container->get(OrganizationService::class));
        $s->setMilestoneService($container->get(MilestoneService::class));
        $s->setNotificationService($container->get(NotificationService::class));
        $s->setGearmanJobLauncherService($container->get(GearmanJobLauncherService::class));
        $s->setSignatureService($container->get(SignatureService::class));
        return $s;
    }
}