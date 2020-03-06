<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Oscar\Connector\ConnectorSpentSifacOCI;
use Oscar\Entity\Authentification;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\SpentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Crypt\Password\Bcrypt;
use Zend\ServiceManager\ServiceManager;

class OscarSpentSyncAllCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'spent:syncall';

    protected function configure()
    {
        $this
            ->setDescription("Synchronisation des dépenses")
            ->setHelp("")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Synchronisation des dépenses");

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);

        try {
            $connectorConfig = $oscarConfig->getConfiguration('connectors.spent');

            $keysConfig = array_keys($connectorConfig);
            if( count($keysConfig) == 0 ){
                $io->error("Pas de synchronisation des dépenses configuré");
                return;
            }
            elseif (count($keysConfig) > 1) {
                $io->error("Oscar ne prends en charge qu'une source de synchronisation pour les dépenses.");
                return;
            }
            else {
                $conf = $connectorConfig[$keysConfig[0]];
                $class = $conf['class'];
                $factory = new \ReflectionClass($class);

                /** @var ConnectorSpentSifacOCI $instance */
                $instance = $factory->newInstanceArgs([$this->getServicemanager()->get(SpentService::class), $conf['params']]);

                $result = $instance->syncAll($io);
                $io->write($result);
            }

        } catch (\Exception $e ){
            $io->error("Impossible de synchroniser les dépenses : " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return;
        }
    }
}