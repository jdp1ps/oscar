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
use UnicaenSignature\Service\SignatureService;

class ProjectGrantServiceFactory extends AbstractOscarFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
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