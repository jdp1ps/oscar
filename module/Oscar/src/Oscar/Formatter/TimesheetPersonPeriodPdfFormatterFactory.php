<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 24/09/19
 * Time: 10:30
 */

namespace Oscar\Formatter;


use Interop\Container\ContainerInterface;
use Oscar\Service\OscarConfigurationService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TimesheetPersonPeriodPdfFormatterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $renderer = $container->get('ViewRenderer');
        $templatePath = $container->get(OscarConfigurationService::class)->getConfiguration('timesheet_person_month_template');
        return new \Oscar\Formatter\TimesheetPersonPeriodPdfFormatter($templatePath, $renderer);
    }

}