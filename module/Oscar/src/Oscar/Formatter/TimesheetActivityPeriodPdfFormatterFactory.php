<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 24/09/19
 * Time: 10:24
 */

namespace Oscar\Formatter;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Service\OscarConfigurationService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class TimesheetActivityPeriodPdfFormatterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $renderer = $container->get('ViewRenderer');

        /** @var OscarConfigurationService $configOscar */
        $configOscar = $container->get(OscarConfigurationService::class);

        $templatePath = $configOscar->getConfiguration('timesheet_activity_synthesis_template');

        return new \Oscar\Formatter\TimesheetActivityPeriodPdfFormatter($templatePath, $renderer);
    }

}