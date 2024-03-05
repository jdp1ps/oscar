<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 26/09/19
 * Time: 13:12
 */

namespace Oscar\Service;


use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use UnicaenSignature\Service\ProcessService;
use UnicaenSignature\Service\SignatureService;

class ContractDocumentServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new ContractDocumentService();
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setEntityManager($container->get(EntityManager::class));
        $s->setLoggerService($container->get('Logger'));
        $s->setActivityLogService($container->get(ActivityLogService::class));
        $s->setSignatureService($container->get(SignatureService::class));
        $s->setProcessService($container->get(ProcessService::class));
        return $s;
    }

}