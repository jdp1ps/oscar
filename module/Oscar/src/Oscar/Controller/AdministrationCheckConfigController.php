<?php

namespace Oscar\Controller;

use Oscar\Provider\Privileges;
use Psr\Container\ContainerInterface;

class AdministrationCheckConfigController extends AbstractOscarController
{

    private $serviceLocator;

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ContainerInterface $s)
    {
        $this->serviceLocator = $s;
    }

    public function checkConfigHomeAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_MENU_ADMIN);

        $rootPath = __DIR__ . '/../../../../../';
        $configPath = 'config/autoload/local.php';
        $configPathReal = realpath($rootPath . $configPath);
        $configEditablePath = 'config/autoload/oscar-editable.yml';
        $configEditablePathReal = realpath($rootPath . $configEditablePath);

        $php_error = NULL;
        if (PHP_VERSION_ID < 70000) {
            $php_error = 'Major version required !';
        }

        $modulesStatus = [];
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
            $moduleStatus = [];
            $moduleStatus[] = $moduleName;
            $moduleStatus[] = phpversion($moduleName) ?: '???';
            $moduleStatus[] = extension_loaded($moduleName) ? 'Installed' : 'Missing module !!!';

            $modulesStatus[] = $moduleStatus;
        }





        $config_path_error = NULL;
        if (!$configPathReal || !file_exists($configPathReal)) {
            $config_path_error = "ERROR : Le fichier de configuration " . $configPath . " n'existe pas/n'est pas accessible";
        }

        $config_editable_path_error = NULL;
        if (!$configEditablePath || !file_exists($configEditablePathReal)) {
            $config_editable_path_error = "Le fichier de configuration n'existe pas/n'est pas accessible";
        } else if (!is_writable($configEditablePath)) {
            $config_editable_path_error = "Le fichier de configuration n'est pas éditable";
        }


        $logPath = $this->getOscarConfigurationService()->getLoggerFilePath();
        $log_path_error = NULL;
        if (!is_writable($logPath)) {
            $log_path_error = "Le fichier de log n'est pas éditable";
        }

        $config = new \Oscar\Service\ConfigurationParser(
            $this->getOscarConfigurationService()->getConfigArray()
        );
        $db_error = NULL;
        try {
            $db_host = $config->getConfiguration('doctrine.connection.orm_default.params.host');
            $db_connected = $this->getEntityManager()->getConnection()->isConnected();
        } catch (\Exception $e) {
            $db_error = "ERROR DB : " . $e->getMessage();
        }
        
        $db_model_error = NULL;
        $db_model_updates = [];
        try {

            $cmf     = $this->getEntityManager()->getMetadataFactory();
            $classes = $cmf->getAllMetadata();

            $tool = new \Doctrine\ORM\Tools\SchemaTool($this->getEntityManager());
            $saveMode = false;
            $db_model_updates = $tool->getUpdateSchemaSql($classes, $saveMode);

            if (count($db_model_updates) > 0) {
                $db_model_error = 'EXECUTER : php vendor/bin/doctrine-module orm:schema-tool:update --force';
            }
        } catch (\Exception $e) {
            $db_model_error = "ERROR DB : " . $e->getMessage();
        }
        

        $files = [['file' => 'oscar.paths.document_oscar'],
                  ['file' => 'oscar.paths.document_admin_oscar'],
                  ['file' => 'oscar.paths.timesheet_modele'],
                  ['file' => 'oscar.mailer.template'],
                  ['file' => 'oscar.pcru.files_path']];
        
        foreach ($files as & $file) {
            $file_error = NULL;
            try {
                $pathDocument = $config->getConfiguration($file['file']);
                $file['path'] = $pathDocument;

                if (!file_exists($pathDocument)) {
                    $file_error = "Le chemin n'existe pas / inaccessible";
                } else if (!is_readable($pathDocument)) {
                    $file_error = "Chemin inaccessible en lecture.";
                } else if (!is_writable($pathDocument)) {
                    $file_error = "Chemin inacessible en écriture.";
                }

            } catch (\Exception $e) {
                $file_error = "Configuration manquante : " . $e->getMessage();
            }
            $file['error'] = $file_error;
        }



        $mailer_error = NULL;
        $mailer_warning = NULL;
        $mailer_file_path = NULL;
        try {
            $mailer_url_absolute = $config->getConfiguration('oscar.urlAbsolute');
            if ($mailer_url_absolute == "http://localhost:8080") {
                $mailer_url_absolute = '!DEV! http://localhost:8080';
            }

            $type_transport = $config->getConfiguration('oscar.mailer.transport.type');
            $type_transport_valid = in_array($type_transport, ['sendmail', 'smtp', 'file']);
            if (!$type_transport_valid) {
                $mailer_error = "Type de transport inconnu '$type_transport'";
            } else {
                switch ($type_transport) {
                    case 'sendmail' :
                        $mailer_warning = "Attention, l'utilisation de SENDMAIL n'est pas testée dans cette version";
                        break;

                    case 'smtp' :
                        $mailer_warning = "Attention, l'utilisation d'un serveur SMTP n'est pas testée dans cette version";
                        break;

                    case 'file' :
                        $mailer_file_path = $config->getConfiguration('oscar.mailer.transport.path');
                        if (!$mailer_file_path || !file_exists($mailer_file_path)) {
                            $mailer_error = "Le chemin n'existe pas / inaccessible : " . ($mailer_file_path ? $mailer_file_path : 'oscar.mailer.transport.path');
                        } else if (!is_readable($mailer_file_path)) {
                            $mailer_error = "Chemin inaccessible en lecture : " . $mailer_file_path;
                        } else if (!is_writable($mailer_file_path)) {
                            $mailer_error = "Chemin inacessible en écriture : " . $mailer_file_path;
                        }
                        break;
                }
            }
        } catch (\Exception $e) {
            $mailer_error = "MAILER CONFIG ERROR : " . $e->getMessage();
        }




        $indexer_error = NULL;
        $indexer_elastic = [];
        try {
            $searchClass = $config->getConfiguration('oscar.strategy.activity.search_engine.class');
            
            // ELASTIC SEARCH
            if ($searchClass == 'Oscar\Strategy\Search\ElasticActivitySearch') {

                $nodesUrl = $config->getConfiguration('oscar.strategy.activity.search_engine.params');

                foreach ($nodesUrl[0] as $url) {

                    $nodeResult = [];
                    $nodeResult['url'] = $url;
                    $nodeResult['error'] = NULL;

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    $infos = curl_exec($curl);
                    if (($error = curl_error($curl))) {
                        $nodeResult['error'] = $error;
                    }
                    curl_close($curl);

                    $indexer_elastic[] = $nodeResult;
                }

            } // LUCENE
            elseif ($searchClass == ActivityZendLucene::class) {
                $params = $config->getConfiguration('oscar.strategy.activity.search_engine.params');
                if (!file_exists($params[0])) {
                    $indexer_error = "Dossier pour l'index de recherche LUCENE n'existe pas / inaccessible";
                } else if (!is_readable($params[0])) {
                    $indexer_error = "Dossier pour l'index de recherche LUCENE inaccessible en lecture.";
                } else if (!is_writable($params[0])) {
                    $indexer_error = "Dossier pour l'index de recherche LUCENE inacessible en écriture.";
                }
            } else {
                $indexer_error = " ~ INDEXEUR : Système de recherche non testable...";
            }

        } catch (\Exception $e) {
            $indexer_error = sprintf(" ! INDEXEUR : Configuration du système de recherche incomplet : %s", $e->getMessage());
        }



        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /// CONNECTORS
        $connector_orga_error = NULL;
        $connectors_orga = [];
        try {
            $connectors_orga = $this->check_connectors($config->getConfiguration('oscar.connectors.organization'), 'organization');
        } catch (\Exception $e) {
            $connector_orga_error = sprintf("ERROR CONNECTOR > ORGANIZATIONS : %s", $e->getMessage());
        }

        $connector_persons_error = NULL;
        $connectors_persons = [];
        try {
            $connectors_persons = $this->check_connectors($config->getConfiguration('oscar.connectors.person'), 'person');
        } catch (\Exception $e) {
            $connector_persons_error = sprintf("ERROR CONNECTOR > PERSONS : %s", $e->getMessage());
        }


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ///
        /// GEARMMAN
        ///
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // On teste la présence du worker
        $oscarWorkerFile = __DIR__ . '/../../../../../config/oscarworker.service';
        $worker_error = NULL;
        if (!file_exists($oscarWorkerFile)) {
            $worker_error = "Le fichier OscarWorker est absent (config/oscarworker.service)";
        }

        $worker_response = NULL;
        try {
            $worker_gearman_host = $this->getOscarConfigurationService()->getGearmanHost();

            $client = new \GearmanClient();
            try {
                $client->addServer($worker_gearman_host);
                $client->setTimeout(1000);
                if (!($worker_response = @$client->doHigh(
                    'hello',
                    json_encode(['message' => 'Check Config Oscar']),
                    'check-config'
                ))) {
                    $worker_error =
                        "LE WORKER NE RÉAGIT PAS, Vérifiez qu'il est bien lancé, si vous avez réalisé une mise à jour, pensez à relancer le service.\n Gearman a répondu : " . $client->error();
                }
            } catch (\Exception $e) {
                $worker_error =
                    "GEARMAN FAIL, Impossible de se connecter au serveur Gearman '" . $worker_gearman_host . "' : \n Erreur : " . $client->error() . " " . $e->getMessage();
            }

        } catch (\Exception $e) {
            $worker_error = sprintf("GEARMAN FAIL, Impossible de se connecter au serveur Gearman : %s", $e->getMessage());
        }

        $ldap_error = NULL;
        try {
            $ldapConfig = $config->getConfiguration('unicaen-app.ldap');

            $options = [];
            foreach ($ldapConfig['connection'] as $name => $connection) {
                $options[$name] = $connection['params'];
            }

            $ldap = new \Laminas\Ldap\Ldap($options['default']);
            $ldap->searchEntries(sprintf($options['default']['accountFilterFormat'], "test"));

        } catch (\Exception $e) {
            $ldap_error = "LDAP FAIL, Impossible de se connecter au serveur LDAP : \n Erreur : " . $e;
        }

        return [
            'build' => \Oscar\OscarVersion::getBuild(),
            'config_path_real' => $configPathReal,
            'uname' => php_uname(),
            'php_version' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION . ' (' . phpversion() . ')',
            'php_ini' => php_ini_loaded_file(),
            'php_error' => $php_error,
            'modules_status' => $modulesStatus,
            'config_path' => $configPath,
            'config_path_error' => $config_path_error,
            'config_editable_path' => $configEditablePath,
            'config_editable_path_error' => $config_editable_path_error,
            'log_path' => $logPath,
            'log_path_error' => $log_path_error,
            'db_error' => $db_error,
            'db_host' => $db_host,
            'db_connected' => $db_connected,
            'db_model_error' => $db_model_error,
            'db_model_updates' => $db_model_updates,
            'files' => $files,
            'mailer_error' => $mailer_error,
            'mailer_url_absolute' => $mailer_url_absolute,
            'type_transport' => $type_transport,
            'type_transport_valid' => $type_transport_valid,
            'mailer_file_path' => $mailer_file_path,
            'indexer_error' => $indexer_error,
            'indexer_elastic' => $indexer_elastic,
            'connector_orga_error' => $connector_orga_error,
            'connectors_orga' => $connectors_orga,
            'connector_persons_error' => $connector_persons_error,
            'connectors_persons' => $connectors_persons,
            'worker_error' => $worker_error,
            'worker_gearman_host' => $worker_gearman_host,
            'worker_response' => $worker_response,
            'ldap_error' => $ldap_error
        ];
    }

    private function check_connectors($connectors, $type) {
        $connectors_info = [];

        foreach ($connectors as $key => $params) {

            $connectors_info[$key] = [];
            $connectors_info[$key]['error'] = NULL;
            $connectors_info[$key]['count'] = NULL;
            $connectors_info[$key]['params'] = [];

            $connectors_info[$key]['key'] = $key;
            $connectors_info[$key]['class'] = $params['class'];

            try {
                // Options du connecteur
                $fileYml = $params['params'];
                $connectors_info[$key]['config'] = $fileYml;

                /** @var AbstractConnectorOscar $class */
                $class = new $params['class'];

                if (!file_exists($fileYml)) {
                    $connectors_info[$key]['error'] = "Fichier de configuration du connector n'existe pas / inaccessible";
                    continue;
                } else if (!is_readable($fileYml)) {
                    $connectors_info[$key]['error'] = "Fichier de configuration du connector inaccessible en lecture.";
                    continue;
                }

                $class->init($this->getServiceLocator(), $fileYml, $key);

                $parser = new \Symfony\Component\Yaml\Parser();
                $connectors_info[$key]['params'] = $parser->parse(file_get_contents($fileYml));

                $connectors_info[$key]['count'] = $class->checkAccess();

            } catch (\Exception $e) {
                $connectors_info[$key]['error'] = sprintf("ERROR CONNECTOR > %s : %s", $type, $e->getMessage());
            }
        }

        return $connectors_info;
    }
}
