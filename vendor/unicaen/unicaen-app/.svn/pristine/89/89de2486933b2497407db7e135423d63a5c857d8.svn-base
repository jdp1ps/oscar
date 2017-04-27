<?php

namespace UnicaenApp\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use UnicaenApp\View\Renderer\CsvRenderer;

/**
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class ViewCsvRendererFactory implements FactoryInterface
{
    /**
     * Create and return the Csv view renderer
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return CsvRenderer
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $csvRenderer = new CsvRenderer();
        return $csvRenderer;
    }
}
