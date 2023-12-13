<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaValidator;
use Monolog\Logger;
use Oscar\Connector\AbstractConnectorOscar;
use Oscar\Exception\OscarException;
use Oscar\OscarVersion;
use Oscar\Service\ConfigurationParser;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Parser;

class OscarCheckConfigCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'check:config';

    protected function configure()
    {
        $this
            ->setDescription("Vérification de la configuration");
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

    protected function checkConnectorPerson(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Configuration du connecteur PERSON");

        /** @var OscarConfigurationService $config */
        $config = $this->getOscarConfiguration();

        $connectors = $config->getConfiguration('connectors.person');

        foreach ($connectors as $key => $params) {
            $io->text("-------------------------------------------------------");
            $io->text(sprintf("# Configuration : <bold>%s</bold>", $key));

            // Class
            $io->text(sprintf("class: <bold>%s</bold>", $params['class']));
            $io->text("---");


            // Options du connecteur

            $fileYml = $params['params'];

            /** @var AbstractConnectorOscar $class */
            $class = new $params['class'];

            if ($this->checkPath($io, $fileYml, "Fichier de configuration du connector", 'r')) {
                $class->init($this->getServicemanager(), $fileYml, $key);
                $parser = new Parser();
                $paramsPhp = $parser->parse(file_get_contents($fileYml));
                foreach ($paramsPhp as $paramKey => $paramValue) {
                    $io->text(sprintf('  + %s : <bold>%s</bold>', $paramKey, $paramValue));
                }

                $io->write(sprintf('* Accès au connecteur <bold>%s</bold>', $paramsPhp['url_persons']));

                if ($class->checkAccess()) {
                    $io->write(" <green>OK</green>");
                } else {
                    $io->write(" <error>ERROR !</error>");
                }
                $io->newLine();
            }
            $io->text("-------------------------------------------------------");
        }
    }

    protected function checkConnectorOrganization(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Configuration du connecteur ORGANIZATION");

        /** @var OscarConfigurationService $config */
        $config = $this->getOscarConfiguration();

        $connectors = $config->getConfiguration('connectors.organization');

        foreach ($connectors as $key => $params) {
            $io->text(sprintf("Configuration : <bold>%s</bold>", $key));

            // Class
            $io->text(sprintf("class: <bold>%s</bold>", $params['class']));

            // Options du connecteur
            $fileYml = $params['params'];

            /** @var AbstractConnectorOscar $class */
            $class = new $params['class'];

            if ($this->checkPath($io, $fileYml, "Fichier de configuration du connector", 'r')) {
                $class->init($this->getServicemanager(), $fileYml, $key);
                $parser = new Parser();
                $paramsPhp = $parser->parse(file_get_contents($fileYml));
                foreach ($paramsPhp as $paramKey => $paramValue) {
                    $io->text(sprintf('  + %s : <bold>%s</bold>', $paramKey, $paramValue));
                }

                $io->write(sprintf('* Accès au connecteur <bold>%s</bold>', $paramsPhp['url_organization']));

                if ($class->checkAccess()) {
                    $io->write(" <green>OK</green>");
                } else {
                    $io->write(" <error>ERROR !</error>");
                }
                $io->newLine();
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $this->getLogger()->info("[COMMAND] check:config");

        $io = new SymfonyStyle($input, $output);


        $io->title("Vérification de la configuration");


        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);


        $rootPath = __DIR__ . '/../../../../../';
        $configPath = 'config/autoload/local.php';
        $configPathReal = realpath($rootPath . $configPath);
        $configEditablePath = 'config/autoload/oscar-editable.yml';
        $configEditablePathReal = realpath($rootPath . $configEditablePath);

        $io->writeln("N°Version : <bold>" . OscarVersion::getBuild() . "</bold>");
        $io->writeln("Configuration : <bold>" . $configPathReal . "</bold>");
        $io->writeln("System : <bold>" . php_uname() . "</bold>");


        $io->section("PHP Requirements : ");
        //php_ini_loaded_file

        $io->write(
            " - PHP version : <bold>" . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION . "</bold> (<bold>" . phpversion(
            ) . "</bold>)"
        );
        if (PHP_VERSION_ID < 70000) {
            $io->error('Major version required !');
            return self::FAILURE;
        } else {
            $io->write("<green>OK</green>\n");
        }

        $io->writeln("\n - php.ini ");
        $io->writeln(php_ini_loaded_file());

        $io->section("Modules");


        $modulesRequires = [
            'bz2',
            'curl',
            'fileinfo',
            'gd',
            'gearman',
            'iconv',
            'json',
            'ldap',
            'mbstring',
            'openssl',
            'pdo_pgsql',
            'posix',
            'Reflection',
            'session',
            'xml',
            'zip'
        ];
        foreach ($modulesRequires as $moduleName) {
            $row[] = $this->checkModule($moduleName, $io);
        }

        $io->table(['Modules PHP', 'Version', 'Statut'], $row);

        $io->section(" OSCAR configuration : ");

        $io->write(" - Configuration LOCAL <bold>$configPath</bold> : ");

        if ($configPathReal && !file_exists($configPathReal)) {
            $io->error("ERROR : Le fichier de configuration '$configPath' n'existe pas/n'est pas accessible");
            return self::FAILURE;
        }
        $io->writeln("<green>OK</green>");

        $io->write(" - Configuration éditable (<bold>$configEditablePath</bold>) :  ");
        if ($configEditablePathReal && !file_exists($configEditablePathReal)) {
            $io->error("Le fichier de configuration n'existe pas/n'est pas accessible");
            return self::FAILURE;
        }
        if (!is_writable($configEditablePath)) {
            $io->error("Le fichier de configuration n'est pas éditable");
            return self::FAILURE;
        }
        $io->writeln("<green>OK</green>");

        $logPath = $oscarConfig->getLoggerFilePath();
        $io->write(" - Fichier de LOG (<bold>$logPath</bold>) :  ");
        if (!is_writable($logPath)) {
            $io->error("Le fichier de log n'est pas éditable");
            return self::FAILURE;
        }
        $io->writeln("<green>OK</green>");

        $config = new ConfigurationParser(
            $this->getServicemanager()->get(OscarConfigurationService::class)->getConfigArray()
        );
        $em = $this->getServicemanager()->get(EntityManager::class);

        try {
            $io->write(" - Accès à la base de données ");
            $io->write($config->getConfiguration('doctrine.connection.orm_default.params.host'));
            $io->write(" ... ");

            if ($em->getConnection()->isConnected()) {
                $io->writeln("<green>OK</green>");
            }

            $validator = new SchemaValidator($em);
            $errors = $validator->validateMapping();

            $io->write(" - Modèle de donnée ");
            if (count($errors) > 0) {
                $io->warning("Obsolète");
                foreach ($errors as $error) {
                    $io->error(" - " . print_r($error));
                }
                $io->error("EXECUTER : php vendor/bin/doctrine-module orm:schema-tool:update --force");
                return self::FAILURE;
            } else {
                $io->writeln("<green>OK</green>");
            }
        } catch (\Exception $e) {
            $io->writeln("ERROR DB : " . $e->getMessage());
            return self::FAILURE;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // DOSSIERS / FICHIERS
        try {
            $io->section("Emplacements des fichiers/Dossiers : ");

            $pathDocuments = $config->getConfiguration('oscar.paths.document_oscar');
            $this->checkPath($io, $pathDocuments, "Stoquage des documents > ACTIVITÉS");

            $pathDocuments = $config->getConfiguration('oscar.paths.document_admin_oscar');
            $this->checkPath($io, $pathDocuments, "Stoquage des documents > ADMINISTRATIFS");

            $pathDocuments = $config->getConfiguration('oscar.paths.timesheet_modele');
            $this->checkPath($io, $pathDocuments, "Modèle de document > FEUILLE DE TEMPS");

            $pathDocuments = $config->getConfiguration('oscar.mailer.template');
            $this->checkPath($io, $pathDocuments, "Modèle de mail > TEMPLATE");

            $pathDocuments = $config->getConfiguration('oscar.pcru.files_path');
            $this->checkPath($io, $pathDocuments, "Documents temporaires PCRU");
        } catch (OscarException $e) {
            $io->error(sprintf("Configuration manquante : %s", $e->getMessage()));
            return self::FAILURE;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // MAILER
        try {
            $io->section(" ### Configuration du mailer : ");

            $urlAbsolute = $config->getConfiguration('oscar.urlAbsolute');
            $io->write(" - URL absolue (Utilisée pour forger les liens) : ");
            if ($urlAbsolute == "http://localhost:8080") {
                $io->writeln('<id> !DEV! ' . $urlAbsolute . '</id>');
            } else {
                $io->writeln($urlAbsolute);
            }

            $io->write(" - Transport : ");
            $typeTransport = $config->getConfiguration('oscar.mailer.transport.type');
            $typeTransportValid = in_array($typeTransport, ['sendmail', 'smtp', 'file']);

            if ($typeTransportValid) {
                $io->writeln("<green>$typeTransport</green>");
                switch ($typeTransport) {
                    case 'sendmail' :
                        $io->writeln("Attention, l'utilisation de SENDMAIL n'est pas testée dans cette version");
                        //
                        break;

                    case 'smtp' :
                        $io->writeln("Attention, l'utilisation d'un serveur SMTP n'est pas testée dans cette version");
                        //
                        break;

                    case 'file' :
                        $pathDocuments = $config->getConfiguration('oscar.mailer.transport.path');
                        $this->checkPath($io, $pathDocuments, "Dossier où sont archivés les mails (DEBUG)");
                        break;
                }
            } else {
                $io->writeln("Type de transport inconnu '$typeTransport''");
            }

            $io->writeln("");
        } catch (OscarException $e) {
            $io->error(sprintf("Configuration manquante : %s", $e->getMessage()));
            return self::FAILURE;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// MOTEUR de RECHERCHE

        $io->section(" ### Système d'indexation des activités : ");

        try {
            $searchClass = $config->getConfiguration('oscar.strategy.activity.search_engine.class');


            // ELASTIC SEARCH
            if ($searchClass == 'Oscar\Strategy\Search\ElasticActivitySearch') {
                $io->write(" * Moteur Elastic Search ");
                $nodesUrl = $config->getConfiguration('oscar.strategy.activity.search_engine.params');


                foreach ($nodesUrl[0] as $url) {
                    $io->write("Noeud $url ");

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $infos = curl_exec($curl);
                    if (($error = curl_error($curl))) {
                        $io->write("<error>Error : " . $error . "</error>");
                    } else {
                        $io->write("<green>OK (Response 200)</green>");
                    }
                    curl_close($curl);
                }
            } // LUCENE
            elseif ($searchClass == ActivityZendLucene::class) {
                $params = $config->getConfiguration('oscar.strategy.activity.search_engine.params');
                $this->checkPath($params[0], "Dossier pour l'index de recherche LUCENE");
            } else {
                $io->warning(" ~ INDEXEUR : Système de recherche non testable...");
            }
        } catch (OscarException $e) {
            $io->error(sprintf(" ! INDEXEUR : Configuration du système de recherche incomplet : %s", $e->getMessage()));
            return self::FAILURE;
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// CONNECTORS
        try {
            $this->checkConnectorOrganization($input, $output);
        } catch (OscarException $e) {
            $io->warning(sprintf(" ~ CONNECTOR > ORGANIZATIONS : Pas de connecteur person : %s", $e->getMessage()));
        }

        try {
            $this->checkConnectorPerson($input, $output);
        } catch (OscarException $e) {
            echo $e->getTraceAsString();
            $io->warning(sprintf(" ~ CONNECTOR > PERSONS : Pas de connecteur person : %s", $e->getMessage()));
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///
        /// GEARMMAN
        ///
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $io->section(" ### GEARMAN : ");


        // On teste la présence du worker
        $oscarWorkerFile = __DIR__ . '/../../../../../config/oscarworker.service';
        if (!file_exists($oscarWorkerFile)) {
            $io->error("Le fichier OscarWorker est absent (config/oscarworker.service)");
            return self::FAILURE;
        }

        $io->write(sprintf("* Envoi d'un job 'HELLO' à Gearman sur '%s' : ", $oscarConfig->getGearmanHost()));
        $client = new \GearmanClient();
        try {
            $client->addServer($oscarConfig->getGearmanHost());
            $client->setTimeout(1000);
            if (!($response = @$client->doHigh(
                'hello',
                json_encode(['message' => 'Check Config Oscar']),
                'check-config'
            ))) {
                $io->error(
                    "LE WORKER NE RÉAGIT PAS, Vérifiez qu'il est bien lancé, si vous avez réalisé une mise à jour, pensez à relancer le service.\n Gearman a répondu : " . $client->error(
                    )
                );
                return self::FAILURE;
            } else {
                $io->writeln("OscarWorker a répondu : '<green>$response</green>'");
            }
        } catch (\Exception $e) {
            $io->error(
                "GEARMAN FAIL, Impossible de se connecter au serveur Gearman '" . $oscarConfig->getGearmanHost(
                ) . "' : \n Erreur : " . $client->error()
            );
            return self::FAILURE;
        }
        return self::SUCCESS;
    }

    protected function checkPath(SymfonyStyle $io, $path, $text, $level = 'error', $allowed = 'rw')
    {
        $io->write(" - Path <bold>$path</bold> : ");

        if (!file_exists($path)) {
            $io->writeln("<error>Le chemin n'existe pas / inaccessible</error>");
            return false;
        }

        if (!is_readable($path)) {
            $io->writeln("<error>Chemin inaccessible en lecture.</error>");
            return false;
        }

        if (strpos($allowed, 'w') > -1 && !is_writable($path)) {
            $io->writeln("<error>Chemin inacessible en écriture.</error>");
            return false;
        }

        $io->writeln("<green>OK!</green>");
        return true;
    }

    protected function checkModule($module, SymfonyStyle $io, $level = 'error')
    {
        $badOut = $level == 'warn' ? 'warning' : 'error';
        $msg = $level == 'warn' ? "WARNING" : "ERROR";
        $out = [];

        $out[] = "<bold>$module</bold>";
        $out[] = phpversion($module) ?: '???';

        if (!extension_loaded($module)) {
            $out[] = "<error>Missing $module !!!</error>";
        } else {
            $out[] = "<green>Installed</green>";
        }
        return $out;
    }
}