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
use Moment\Moment;
use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\Person;
use Oscar\Entity\Role;
use Oscar\OscarVersion;
use Oscar\Service\ConfigurationParser;
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
use Symfony\Component\Yaml\Parser;
use Zend\Config\Reader\Yaml;

class OscarCheckConfigCommand extends OscarCommandAbstract
{
    protected static $defaultName = 'oscar:check:config';

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
        $io->writeln("Configuration : <bold>". $configPathReal ."</bold>");
        $io->writeln("System : <bold>".php_uname()."</bold>");


        $io->section("PHP Requirements : ");
        //php_ini_loaded_file

        $io->write(" - PHP version : <bold>".PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION."</bold> (<bold>".phpversion()."</bold>)");
        if( PHP_VERSION_ID < 70000 ) {
            $io->error('Major version required !');
            return;
        }
        else
            $io->write("<green>OK</green>\n");

        $io->writeln("\n - php.ini ");
        $io->writeln(php_ini_loaded_file());

        $io->section("Modules");



        $modulesRequires = ['bz2', 'curl', 'fileinfo', 'gd', 'iconv', 'json', 'ldap', 'mbstring', 'openssl', 'pdo_pgsql', 'posix', 'Reflection', 'session', 'xml', 'zip', 'toto'];
        foreach ($modulesRequires as $moduleName) {
            $row[] = $this->checkModule($moduleName, $io);
        }

        $io->table(['Modules PHP', 'Version', 'Statut'], $row);

        $io->section(" OSCAR configuration : ");

        $io->write(" - Configuration LOCAL <bold>$configPath</bold> : ");

        if( $configPathReal && !file_exists($configPathReal) ){
            $io->error("ERROR : Le fichier de configuration '$configPath' n'existe pas/n'est pas accessible");
            return;
        }
        $io->writeln("<green>OK</green>");

        $io->write(" - Configuration éditable (<bold>$configEditablePath</bold>) :  ");
        if( $configEditablePathReal && !file_exists($configEditablePathReal) ){
            $io->error("Le fichier de configuration n'existe pas/n'est pas accessible");
            return;
        }
        if( !is_writable($configEditablePath) ){
            $io->error("Le fichier de configuration n'est pas éditable");
            return;
        }
        $io->writeln("<green>OK</green>");



        $config = new ConfigurationParser($this->getServicemanager()->get(OscarConfigurationService::class)->getConfigArray());
        $em = $this->getServicemanager()->get(EntityManager::class);

        try {
            $io->write(" * Accès à la base de données ");
            $io->write($config->getConfiguration('doctrine.connection.orm_default.params.host'));
            $io->write(" ... ");

            if ($em->getConnection()->isConnected()) {
                $io->writeln("OK");
            }

            $validator = new SchemaValidator($em);
            $errors = $validator->validateMapping();

            $io->write(" * Modèle de donnée ");
            if (count($errors) > 0) {
                $io->warning("Obsolète");
                foreach( $errors as $error ){
                    $io->error(" - " . $error . " - " . print_r($error));
                }
                $io->error("EXECUTER : php vendor/bin/doctrine-module orm:schema-tool:update --force");
            } else {
                $io->success("OK");
            }

        } catch (\Exception $e ){
            $io->writeln("ERROR " . $e->getMessage());
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

        } catch ( OscarException $e ){
            $io->error(sprintf("Configuration manquante : %s", $e->getMessage()));
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // MAILER
        try {
            $io->section(" ### Configuration du mailer : ");

            $urlAbsolute = $config->getConfiguration('oscar.urlAbsolute');
            $io->write(" * URL absolue : ");
            if( $urlAbsolute == "http://localhost:8080" ){
                $io->write('<bold> !DEV! ' . $urlAbsolute .'</bold>');
            } else {
                $io->write($urlAbsolute);
            }

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
            $io->error(sprintf("Configuration manquante : %s", $e->getMessage()));
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// MOTEUR de RECHERCHE

        $io->section(" ### Système d'indexation des activités : ");

        try {
            $searchClass = $config->getConfiguration('oscar.strategy.activity.search_engine.class');


            // ELASTIC SEARCH
            if( $searchClass == 'Oscar\Strategy\Search\ActivityElasticSearch' ){

                $io->write(" * Moteur Elastic Search ");
                $nodesUrl = $config->getConfiguration('oscar.strategy.activity.search_engine.params');


                foreach ($nodesUrl[0] as $url ){
                    $io->write("Noeud $url ");

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $infos = curl_exec($curl);
                    if( ($error = curl_error($curl)) ){
                        $io->write("<error>Error : " . $error ."</error>");
                    } else {
                        $io->write("<green>OK (Response 200)</green>");
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
                $io->warning(" ~ INDEXEUR : Système de recherche non testable...");
            }
        } catch ( OscarException $e ){
            $io->error(sprintf(" ! INDEXEUR : Configuration du système de recherche incomplet : %s", $e->getMessage()));
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// CONNECTORS
        try {
            $connectorsOrganisation = $config->getConfiguration('oscar.connectors.organization');
            foreach ($connectorsOrganisation as $conn=>$params) {
                $connecteurName = sprintf(" * CONNECTEUR ORGANISATION '%s'", $conn);

                $io->section(" ### Connecteur ORGANIZATION $conn : ");
                $class = $config->getConfiguration("oscar.connectors.organization.$conn.class");
                $params = $config->getConfiguration("oscar.connectors.organization.$conn.params");

                if ($this->checkPath($io, $params, "Fichier de configuration", 'r') ){
                    $parser = new Parser();
                    $paramsPhp = $parser->parse(file_get_contents($params));
                    $io->write(sprintf('* Accès au connecteur <bold>%s</bold>', $paramsPhp['url_organizations']));
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $paramsPhp['url_organizations']);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $infos = curl_exec($curl);
                    if( ($error = curl_error($curl)) ){
                        $io->write(" <error>ERROR : " . $error ."</error>");
                    } else {
                        $io->write(" <green>OK</green>");
                    }
                    curl_close($curl);
                }
            }

        } catch ( OscarException $e ){
            $io->warning(sprintf(" ~ CONNECTOR > ORGANIZATION : Pas de connecteur organisation : %s", $e->getMessage()));
        }

        try {
            $connectors = $config->getConfiguration('oscar.connectors.person');
            foreach ($connectors as $conn=>$params) {
                $connecteurName = sprintf(" * CONNECTEUR PERSON '%s'", $conn);

                $io->section(" ### Connecteur PERSON $conn : ");


                $class = $config->getConfiguration("oscar.connectors.person.$conn.class");
                $params = $config->getConfiguration("oscar.connectors.person.$conn.params");

                if ($this->checkPath($io, $params, "Fichier de configuration", 'r') ){
                    $parser = new Parser();
                    $paramsPhp = $parser->parse(file_get_contents($params));

                    $io->write(sprintf('* Accès au connecteur <bold>%s</bold>', $paramsPhp['url_persons']));

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $paramsPhp['url_persons']);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $infos = curl_exec($curl);
                    if( ($error = curl_error($curl)) ){
                        $io->write(" <error>ERROR : " . $error ."</error>");
                    } else {
                        $io->write(" <green>OK</green>");
                    }
                    curl_close($curl);
                }
            }

        } catch ( OscarException $e ){
            $io->warning(sprintf(" ~ CONNECTOR > PERSONS : Pas de connecteur person : %s", $e->getMessage()));
        }
    }

    protected function checkPath(SymfonyStyle $io, $path, $text, $level = 'error', $allowed = 'rw'){

        $io->write(" - Path <bold>$path</bold> : ");

        if( !file_exists($path) ){
            $io->writeln("<error>Le chemin n'existe pas / inaccessible</error>");
            return false;
        }

        if( !is_readable($path) ){
            $io->writeln("<error>Chemin inaccessible en lecture.</error>");
            return false;
        }

        if( strpos($allowed, 'w') > -1 && !is_writable($path) ){
            $io->writeln("<error>Chemin inacessible en écriture.</error>");
            return false;
        }

        $io->writeln("<green>OK!</green>");
        return true;
    }

    protected function checkModule($module, SymfonyStyle $io, $level = 'error'){

        $badOut = $level == 'warn' ? 'warning' : 'error';
        $msg = $level == 'warn' ? "WARNING" : "ERROR";
        $out = [];

        $out[] = "<bold>$module</bold>";
        $out[] = phpversion($module) ?: '???';

        if( !extension_loaded($module) ){
            $out[] = "<error>Missing $module !!!</error>";
        } else {
            $out[] = "<green>Installed</green>";
        }
        return $out;
    }
}