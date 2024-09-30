<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 11:52
 */

namespace Oscar\Controller;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Oscar\Service\ActivityLogService;
use Oscar\Service\ActivityRequestService;
use Oscar\Service\ActivityTypeService;
use Oscar\Service\ContractDocumentService;
use Oscar\Service\DocumentFormatterService;
use Oscar\Service\NotificationService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantSearchService;
use Oscar\Service\ProjectGrantService;
use Oscar\Service\ProjectService;
use Oscar\Service\SpentService;
use Oscar\Service\TimesheetService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use UnicaenSignature\Service\SignatureService;

class ProjectGrantControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $c = new ProjectGrantController();

        $c->setLoggerService($container->get('Logger'));
        $c->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $c->setOscarUserContextService($container->get(OscarUserContext::class));
        $c->setEntityManager($container->get(EntityManager::class));
        $c->setActivityRequestService($container->get(ActivityRequestService::class));
        $c->setOrganizationService($container->get(OrganizationService::class));
        $c->setActivityTypeService($container->get(ActivityTypeService::class));
        $c->setTimesheetService($container->get(TimesheetService::class));
        $c->setActivityLogService($container->get(ActivityLogService::class));
        $c->setNotificationService($container->get(NotificationService::class));
        $c->setPersonService($container->get(PersonService::class));
        $c->setOrganizationService($container->get(OrganizationService::class));
        $c->setActivityService($container->get(ProjectGrantService::class));
        $c->setProjectService($container->get(ProjectService::class));
        $c->setSpentService($container->get(SpentService::class));
        $c->setContractDocumentService($container->get(ContractDocumentService::class));
        $c->setDocumentFormatterService($container->get(DocumentFormatterService::class));
        $c->setSignatureService($container->get(SignatureService::class));
        $c->setProjectGrantSearchService($container->get(ProjectGrantSearchService::class));
        $c->setServiceContainer($container);
        return $c;
    }
}