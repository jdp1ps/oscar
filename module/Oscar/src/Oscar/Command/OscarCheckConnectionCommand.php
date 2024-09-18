<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaValidator;
use Monolog\Logger;
use Oscar\Connector\AbstractConnectorOscar;
use Oscar\Exception\OscarException;
use Oscar\OscarVersion;
use Oscar\Service\ConfigurationParser;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Parser;

class OscarCheckConnectionCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'check:connection';

    protected function configure()
    {
        $this
            ->setDescription("Vérification de la configuration")
            ->addArgument('name', InputArgument::OPTIONAL, 'Nom de la connection', null);
    }

    /**
     * @return OscarConfigurationService
     */
    protected function getOscarConfiguration()
    {
        return $this->getServicemanager()->get(OscarConfigurationService::class);
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->getServicemanager()->get('Logger');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $config = $this->getServicemanager()->get('Config');
        $this->addOutputStyle($output);
        $io = new SymfonyStyle($input, $output);

        $database_config = $config['doctrine']['connection'];
        $available_connections = array_keys($database_config);
        if ($name != null && !in_array($name, $available_connections)) {
            $io->error("Connection '$name' n'existe pas'");
            return self::FAILURE;
        }
        else {
            if ($name == null) {
                $conn = $database_config;
            } else {
                $conn = ["$name" => $database_config[$name]];
            }
        }

        foreach ($conn as $c_name=>$c_conf) {
            $io->info($c_name);

            $co = DriverManager::getConnection($c_conf['params']);
            try {
                if( $co->connect() ) {
                    $io->success("Connection à '$c_name' réussie");
                } else {
                    $io->warning("Connection refusée");
                }
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        }
        return self::FAILURE;
    }
}