<?php

namespace UnicaenApp\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use UnicaenApp\View\Strategy\CsvStrategy;

class ViewCsvStrategyFactory implements FactoryInterface
{
    /**
     * Create and return the CSV view strategy
     *
     * Retrieves the ViewCsvRenderer service from the service locator, and
     * injects it into the constructor for the CSV strategy.
     *
     * It then attaches the strategy to the View service, at a priority of 100.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return CsvStrategy
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $csvRenderer = $serviceLocator->get('ViewCsvRenderer');
        $csvStrategy = new CsvStrategy($csvRenderer);
        return $csvStrategy;
    }
}
