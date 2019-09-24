<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 24/09/19
 * Time: 10:26
 */

namespace Oscar\Formatter;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Oscar\Service\OscarConfigurationService;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class TimesheetPersonPeriodHtmlFormatterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $renderer = $container->get('ViewRenderer');
        $templatePath = $container->get(OscarConfigurationService::class)->getConfiguration('timesheet_person_month_template');
        return new \Oscar\Formatter\TimesheetPersonPeriodHtmlFormatter($templatePath, $renderer);
    }
}