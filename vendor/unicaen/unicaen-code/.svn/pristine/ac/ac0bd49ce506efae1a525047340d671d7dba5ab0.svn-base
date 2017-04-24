<?php

namespace UnicaenCode\Service;

use Serializable;
use Zend\Mvc\MvcEvent;
use UnicaenCode\Service\Traits\ConfigAwareTrait;
use ZendDeveloperTools\Collector\CollectorInterface;

/**
 * Collecteur de données UnicaenCode
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class Collector implements CollectorInterface, Serializable
{
    use ConfigAwareTrait;

    const NAME     = 'unicaen-code_collector';

    const PRIORITY = 150;



    public function getViews()
    {
        $viewDirs = $this->getServiceConfig()->getViewDirs();
        $viewFiles = [];
        foreach( $viewDirs as $viewDir ){
            $files = scandir($viewDir, SCANDIR_SORT_ASCENDING);
            foreach( $files as $file ){
                $viewFiles[] = $file;
            }
        }
        $viewFiles = array_unique($viewFiles);
        $items = [];
        foreach ($viewFiles as $viewFile) {
            if ($viewFile != '.' && $viewFile != '..') {
                if (false !== strrpos($viewFile, '.php')){
                    $items[] = substr($viewFile, 0, strrpos($viewFile, '.php'));
                }
            }
        }
        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return static::PRIORITY;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(MvcEvent $mvcEvent)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {

    }
}
