<?php

namespace Oscar\Service\administration;

class CheckConfigService
{
    public function checkModel()
    {
    }

    public function checkSchema()
    {
    }

    /**
     * Controle de l'accès à la base de données
     * @return void
     */
    public function checkDatabase()
    {
    }

    public function checkPHPModules()
    {
        $modules = [
            'bz2' => 'Module de compression/décompression BZIP2 (libbz2)',
            'curl' => '',
            'fileinfo' => '',
            'gd' => '',
            'gearman' => '',
            'iconv' => '',
            'json' => '',
            'ldap' => '',
            'mbstring' => '',
            'openssl' => '',
            'pdo_pgsql' => '',
            'posix' => '',
            'Reflection' => '',
            'session' => '',
            'xml' => '',
            'zip' => ''
        ];

        $out = [];
        foreach ($modules as $module=>$description) {
            $out[] = $this->checkModule($module, $description);
        }

        return $out;
    }

    public function checkModule(string $moduleName, string $description): CheckConfigItem
    {
        $check = new CheckConfigItem($moduleName, $description);
        $version = phpversion($moduleName);
        $check->setValue($version);
        $loaded = extension_loaded($moduleName);
        if (!$version) {
            $check->setStatus(CheckConfigItem::STATUS_ERROR);
            $check->addError("Module non installé");
            if (!$loaded) {
                $check->setStatus(CheckConfigItem::STATUS_ERROR);
                $check->addError("Module non chargé");
            }
        } else {
            $check->setStatus(CheckConfigItem::STATUS_SUCCESS);
        }
        return $check;
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private $serviceContainer;

    public function setServiceContainer(\Interop\Container\ContainerInterface $container)
    {
        $this->serviceContainer = $container;
    }

    private $entityManager;

    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private $loggerService;

    public function setLoggerService($loggerService)
    {
        $this->loggerService = $loggerService;
    }
}