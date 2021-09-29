<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 03/10/19
 * Time: 16:18
 */

namespace Oscar\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\ServiceManager\ServiceManager;

abstract class OscarCommandAbstract extends Command
{

    const COMMAND_ACTIVITY_SEARCH_REINDEX = 'activity:search:reindex';
    const COMMAND_PERSON_SEARCH_REINDEX = 'person:search:reindex';
    const COMMAND_ORGANIZATION_SEARCH_REINDEX = 'organization:search:reindex';

    /** @var ServiceManager ServiceManager */
    private $servicemanager;

    /**
     * OscarCommandAbstract constructor.
     */
    public function __construct(ServiceManager $sm)
    {
        $this->servicemanager = $sm;
        parent::__construct();
    }

    /**
     * @return ServiceManager
     */
    protected function getServicemanager(){
        return $this->servicemanager;
    }



    public function addOutputStyle(OutputInterface $output) {
        $outputStyle = new OutputFormatterStyle('cyan', 'default', ['bold']);
        $output->getFormatter()->setStyle('id', $outputStyle);

        $outputStyle = new OutputFormatterStyle('blue', 'default', ['underscore']);
        $output->getFormatter()->setStyle('link', $outputStyle);

        $outputStyle = new OutputFormatterStyle('default', 'default', ['bold']);
        $output->getFormatter()->setStyle('bold', $outputStyle);

        $outputStyle = new OutputFormatterStyle('yellow', 'default', ['bold']);
        $output->getFormatter()->setStyle('none', $outputStyle);

        $outputStyle = new OutputFormatterStyle('cyan', 'default', ['bold']);
        $output->getFormatter()->setStyle('title', $outputStyle);

        $outputStyle = new OutputFormatterStyle('green', 'default', ['bold']);
        $output->getFormatter()->setStyle('green', $outputStyle);
    }
}