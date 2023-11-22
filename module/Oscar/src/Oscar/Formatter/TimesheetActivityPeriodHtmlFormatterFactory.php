<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 24/09/19
 * Time: 10:23
 */

namespace Oscar\Formatter;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Oscar\Service\OscarConfigurationService;

class TimesheetActivityPeriodHtmlFormatterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $renderer = $container->get('ViewRenderer');
        $templatePath = $container->get(OscarConfigurationService::class)->getConfiguration('timesheet_activity_synthesis_template');
        return new Timesheet\TimesheetActivityPeriodHtmlFormatter($templatePath, $renderer);
    }
}