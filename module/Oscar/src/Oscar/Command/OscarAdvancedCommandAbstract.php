<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 03/10/19
 * Time: 16:18
 */

namespace Oscar\Command;

use Monolog\Logger;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\PersonService;
use Oscar\Service\ProjectGrantService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class OscarAdvancedCommandAbstract extends OscarCommandAbstract
{
    const OPTION_FORCE = 'force';
    const OPTION_VERBOSE = 'verbose';

    /** @var InputInterface */
    private InputInterface $input;

    /** @var OutputInterface */
    private OutputInterface $output;

    /** @var SymfonyStyle */
    private SymfonyStyle $io;

    protected function configure()
    {
        $this->addOption(
            self::OPTION_FORCE,
            'f',
            InputOption::VALUE_OPTIONAL,
            "Mode non-interactif",
            false
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        $this->addOutputStyle($output);

        return 0;
    }

    protected function getIO(): SymfonyStyle
    {
        return $this->io;
    }

    protected function isInteractive(): bool
    {
        return !$this->isForce() || $this->getIO()->isQuiet();
    }

    protected function isForce(): bool
    {
        return $this->input->getOption(self::OPTION_FORCE) === null;
    }

    protected function isQuiet(): bool
    {
        return $this->io->isQuiet();
    }

    /**
     * Affiche la question et attend une réponse (retourne TRUE si la réponse et OUI). La réponse est toujours TRUE
     * en mode forcé (--force) ou silencieux (--quiet)
     *
     * @param string $question
     * @return bool
     */
    protected function ask(string $question): bool
    {
        if( $this->isInteractive() ){
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion($question, false);
            return $helper->ask($this->input, $this->output, $question);
        } else {
            return true;
        }
    }

    protected function info(string $message) :void
    {
        $this->logVerbosity($message, OutputInterface::VERBOSITY_VERBOSE);
    }

    protected function notice(string $message) :void
    {
        $this->logVerbosity($message, OutputInterface::VERBOSITY_NORMAL);
    }

    protected function debug(string $message) :void
    {
        $this->logVerbosity($message, OutputInterface::VERBOSITY_VERY_VERBOSE);
    }

    protected function logVerbosity( string $message, int $verbosity ) :void
    {
        if( $this->getIO()->getVerbosity() >= $verbosity ){
            $this->getIO()->writeln($message);
        }
        switch ($verbosity) {
            case OutputInterface::VERBOSITY_NORMAL:
                $this->getLoggerService()->notice($message, [$this->getName()]);
                break;
            case OutputInterface::VERBOSITY_VERBOSE:
                $this->getLoggerService()->info($message, [$this->getName()]);
                break;
            default :
                $this->getLoggerService()->debug($message, [$this->getName()]);
                break;
        }
    }

    /**
     * Erreur final
     *
     * @param \Exception $e
     * @return int
     */
    protected function finalFatalError(\Exception $e): int
    {
        $this->getLoggerService()->error($e->getMessage());
        if( !$this->getIO()->isQuiet() ) {
            $this->getIO()->error($e->getMessage());
        }
        return E_ERROR;
    }

    /**
     * Succès final
     *
     * @param string $message
     * @return int
     */
    protected function finalSuccess( string $message ):int
    {
        $this->getLoggerService()->notice($message);
        if( !$this->io->isQuiet() ){
            $this->getIO()->success($message);
        }
        return 0;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    protected function getOscarConfigurationService(): OscarConfigurationService
    {
        return $this->getServicemanager()->get(OscarConfigurationService::class);
    }

    protected function getProjectGrantService(): ProjectGrantService
    {
        return $this->getServicemanager()->get(ProjectGrantService::class);
    }

    protected function getPersonService(): PersonService
    {
        return $this->getServicemanager()->get(PersonService::class);
    }

    protected function getOrganizationService(): OrganizationService
    {
        return $this->getServicemanager()->get(OrganizationService::class);
    }

    protected function getLoggerService(): Logger
    {
        return $this->getServicemanager()->get('Logger');
    }
}