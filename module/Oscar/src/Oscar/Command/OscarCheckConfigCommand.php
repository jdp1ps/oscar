<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 04/10/19
 * Time: 11:49
 */

namespace Oscar\Command;


use Moment\Moment;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\OscarVersion;
use Oscar\Service\ConnectorService;
use Oscar\Service\OrganizationService;
use Oscar\Service\OscarConfigurationService;
use Oscar\Service\OscarUserContext;
use Oscar\Service\PersonService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class OscarCheckConfigCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'oscar:check:search';

    protected function configure()
    {
        $this
            ->setDescription("Vérification de la configuration")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addOutputStyle($output);

        /** @var OscarUserContext $oscaruserContext */
        $oscaruserContext = $this->getServicemanager()->get(OscarUserContext::class);

        $io = new SymfonyStyle($input, $output);

        $io->title("Vérification de la configuration");

        /** @var OscarConfigurationService $oscarConfig */
        $oscarConfig = $this->getServicemanager()->get(OscarConfigurationService::class);


        $rootPath = __DIR__ . '/../../../../../';
        $configPath = 'config/autoload/local.php';
        $configPathReal = realpath($rootPath.$configPath);
        $configEditablePath = 'config/autoload/oscar-editable.yml';
        $configEditablePathReal = realpath($rootPath.$configEditablePath);






        $io->writeln("N°Version : <bold>". OscarVersion::getBuild() ."</bold>");


        $io->section("PHP Requirements : ");
        //php_ini_loaded_file
        $io->writeln(" - System : <bold>".php_uname()."</bold>");

        $io->writeln(" - PHP version : <bold>".PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION."</bold> (<bold>".phpversion()."</bold>)");
        if( PHP_VERSION_ID < 70000 ) {
            $io->error('Major version required !');
            return;
        }
        else
            $io->write("<green>OK</green>\n");

        $io->writeln(" - php.ini ");
        $io->writeln(php_ini_loaded_file());

        $io->section("Modules PHP");

        $this->checkModule('bz2', $io);
        $this->checkModule('curl', $io);
        $this->checkModule('fileinfo', $io);
        $this->checkModule('gd', $io);
        $this->checkModule('iconv', $io);
        $this->checkModule('json', $io);
        $this->checkModule('ldap', $io, 'warn');
        $this->checkModule('mbstring', $io);
        $this->checkModule('openssl', $io);
        $this->checkModule('pdo_pgsql', $io);
        $this->checkModule('posix', $io, 'warn');
        $this->checkModule('Reflection', $io);
        $this->checkModule('session', $io);
        $this->checkModule('xml', $io);
        $this->checkModule('zip', $io);
        $this->checkModule('toto', $io);

        $io->writeln("");
        $io->writeln(" ### OSCAR configuration : ");

        $io->write(" * Fichier de configuration ");
        $io->write($configPath);
        $io->write(" ... ");

        if( $configPathReal && !file_exists($configPathReal) ){
            $io->writeln("ERROR");
            $this->consoleError("Le fichier de configuration '$configPath' n'existe pas/n'est pas accessible");
            return;
        }
        $io->writeln("$configPathReal OK");

        $io->write(" * Fichier de configuration éditable ");
        $io->write($configEditablePath);
        $io->write(" ... ");

        if( $configEditablePathReal && !file_exists($configEditablePathReal) ){
            $io->writeln("ERROR");
            $this->consoleError("Le fichier de configuration '$configEditablePath' n'existe pas/n'est pas accessible");
            return;
        }
        if( !is_writable($configEditablePath) ){
            $io->writeln("ERROR");
            $this->consoleError("Le fichier de configuration '$configEditablePath' n'est pas éditable");
            return;
        }
        $io->writeln("$configEditablePathReal OK");

        // Chargement de la configuration
        $example = require($configPath);
        $config = new ConfigurationParser($example);

        try {
            $io->write(" * Accès à la base de données ");
            $io->write($config->getConfiguration('doctrine.connection.orm_default.params.host'));
            $io->write(" ... ");

            if ($this->getEntityManager()->getConnection()->isConnected()) {
                $io->writeln("OK");
            }

            $validator = new SchemaValidator($this->getEntityManager());
            $errors = $validator->validateMapping();

            $io->write(" * Modèle de donnée ");
            if (count($errors) > 0) {
                $this->consoleError("Obsolète");
                foreach( $errors as $error ){
                    $this->consoleError(" - " . $error . " - " . print_r($error));
                }
                $this->consoleError("EXECUTER : php vendor/bin/doctrine-module orm:schema-tool:update --force");
            } else {
                $this->consoleSuccess("OK");
            }

        } catch (\Exception $e ){
            $io->writeln("ERROR " . $e->getMessage());
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // DOSSIERS / FICHIERS
        try {
            $io->writeln("");
            $io->writeln(" ### Emplacements des fichiers/Dossiers : ");

            $pathDocuments = $config->getConfiguration('oscar.paths.document_oscar');
            $this->checkPath($pathDocuments, "Stoquage des documents > ACTIVITÉS");

            $pathDocuments = $config->getConfiguration('oscar.paths.document_admin_oscar');
            $this->checkPath($pathDocuments, "Stoquage des documents > ADMINISTRATIFS");

            $pathDocuments = $config->getConfiguration('oscar.paths.timesheet_modele');
            $this->checkPath($pathDocuments, "Modèle de document > FEUILLE DE TEMPS");

            $pathDocuments = $config->getConfiguration('oscar.mailer.template');
            $this->checkPath($pathDocuments, "Modèle de mail > TEMPLATE");

        } catch ( OscarException $e ){
            $this->consoleError(sprintf("Configuration manquante : %s", $e->getMessage()));
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // MAILER
        try {
            $io->writeln("");
            $io->writeln(" ### Configuration du mailer : ");

            $urlAbsolute = $config->getConfiguration('oscar.urlAbsolute');
            $io->write(" * URL absolue : ");
            if( $urlAbsolute == "http://localhost:8080" ){
                $io->write(' !DEV! ' . $urlAbsolute);
            } else {
                $io->write($urlAbsolute);
            }
            $io->writeln("");

            $io->write(" * Transport : ");
            $typeTransport = $config->getConfiguration('oscar.mailer.transport.type');
            $typeTransportValid = in_array($typeTransport, ['sendmail', 'smtp', 'file']);

            if( $typeTransportValid ){
                $io->writeln($typeTransport);
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
                        $this->checkPath($pathDocuments, "Dossier où sont archivés les mails (DEBUG)");
                        break;
                }
            } else {
                $io->writeln("Type de transport inconnu '$typeTransport''");
            }

            $io->writeln("");

        } catch ( OscarException $e ){
            $this->consoleError(sprintf("Configuration manquante : %s", $e->getMessage()));
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// MOTEUR de RECHERCHE
        $io->writeln("");
        $io->writeln(" ### Système d'indexation des activités : ");

        try {
            $searchClass = $config->getConfiguration('oscar.strategy.activity.search_engine.class');

            // ELASTIC SEARCH
            if( $searchClass == ActivityElasticSearch::class ){

                $io->write(" * Moteur Elastic Search ");
                $nodesUrl = $config->getConfiguration('oscar.strategy.activity.search_engine.params');


                foreach ($nodesUrl[0] as $url ){
                    $io->write("Noeud $url ");

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $infos = curl_exec($curl);
                    if( ($error = curl_error($curl)) ){
                        $this->consoleError("Error : " . $error);
                    } else {
                        $this->consoleSuccess("OK (Response 200)");
                    }
                    curl_close($curl);
                }
            }
            // LUCENE
            elseif ($searchClass == ActivityZendLucene::class ){
                $params = $config->getConfiguration('oscar.strategy.activity.search_engine.params');
                $this->checkPath($params[0], "Dossier pour l'index de recherche LUCENE");
            }
            else {
                $this->consoleWarn(" ~ INDEXEUR : Système de recherche non testable...");
            }
        } catch ( OscarException $e ){
            $this->consoleError(sprintf(" ! INDEXEUR : Configuration du système de recherche incomplet : %s", $e->getMessage()));
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// CONNECTORS
        try {
            $connectorsOrganisation = $config->getConfiguration('oscar.connectors.organization');
            foreach ($connectorsOrganisation as $conn=>$params) {
                $connecteurName = sprintf(" * CONNECTEUR ORGANISATION '%s'", $conn);
                $io->writeln("");
                $io->writeln(" ### Connecteur ORGANIZATION $conn : ");
                $class = $config->getConfiguration("oscar.connectors.organization.$conn.class");
                $params = $config->getConfiguration("oscar.connectors.organization.$conn.params");

                if ($this->checkPath($params, "Fichier de configuration", 'r') ){
                    $paramsPhp = Yaml::parse(file_get_contents($params));

                    $io->write(" * Accès au connecteur ");
                    $io->write($paramsPhp['url_organizations']);
                    $io->write(" ... ");

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $paramsPhp['url_organizations']);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $infos = curl_exec($curl);
                    if( ($error = curl_error($curl)) ){
                        $this->consoleError($error);
                    } else {
                        $this->consoleSuccess("OK");
                    }
                    curl_close($curl);
                }
            }

        } catch ( OscarException $e ){
            $this->consoleWarn(sprintf(" ~ CONNECTOR > ORGANIZATION : Pas de connecteur organisation : %s", $e->getMessage()));
        }

        try {
            $connectors = $config->getConfiguration('oscar.connectors.person');
            foreach ($connectors as $conn=>$params) {
                $connecteurName = sprintf(" * CONNECTEUR PERSON '%s'", $conn);

                $io->writeln("");
                $io->writeln(" ### Connecteur PERSON $conn : ");


                $class = $config->getConfiguration("oscar.connectors.person.$conn.class");
                $params = $config->getConfiguration("oscar.connectors.person.$conn.params");

                if ($this->checkPath($params, "Fichier de configuration", 'r') ){
                    $paramsPhp = Yaml::parse(file_get_contents($params));

                    $io->write(" * Accès au connecteur ");
                    $io->write($paramsPhp['url_persons']);
                    $io->write(" ... ");

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $paramsPhp['url_persons']);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $infos = curl_exec($curl);
                    if( ($error = curl_error($curl)) ){
                        $this->consoleError(" ! Connector no response : " . $error);
                    } else {
                        $this->consoleSuccess("OK");
                    }
                    curl_close($curl);
                }
            }

        } catch ( OscarException $e ){
            $this->consoleWarn(sprintf(" ~ CONNECTOR > PERSONS : Pas de connecteur person : %s", $e->getMessage()));
        }
    }

    protected function checkModule($module, SymfonyStyle $io, $level = 'error'){

        $badOut = $level == 'warn' ? 'warning' : 'error';

        $msg = $level == 'warn' ? "WARNING" : "ERROR";

        $io->write(" * Module PHP ");
        $io->write("<bold>$module</bold>");
        $io->write(' ('.phpversion($module).')');
        $io->write(" ... ");

        if( !extension_loaded($module) ){
            if( $level == 'error' ){
                $io->error("Missing $module !!!");
            }
            return false;
        }

        $io->write("<green>Installed</green>\n");
        return true;
    }
}