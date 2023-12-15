<?php

namespace Oscar\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Renderer\PhpRenderer;

class DocumentFormatterServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $s = new DocumentFormatterService();
        $s->setLoggerService($container->get('Logger'));
        $s->setOscarConfigurationService($container->get(OscarConfigurationService::class));
        $s->setViewRenderer($container->get(PhpRenderer::class));
        return $s;
    }
}