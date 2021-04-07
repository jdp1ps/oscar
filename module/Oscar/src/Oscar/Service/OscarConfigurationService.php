<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 22/02/19
 * Time: 11:26
 */

namespace Oscar\Service;


use Monolog\Logger;
use Oscar\Exception\OscarException;
use Oscar\Formatter\File\IHtmlToPdfFormatter;
use Oscar\Import\Data\DataStringArray;
use Oscar\OscarVersion;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class OscarConfigurationService implements ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    const allow_numerotation_custom = 'allow_numerotation_custom';
    const organization_leader_role = 'organization_leader_role';
    const spents_account_filter = 'spents_account_filter';
    const activity_request_limit = 'activity_request_limit';
    const document_use_version_in_name = 'document_use_version_in_name';

    const theme = 'theme';

    public function getApiFormats($default = [])
    {
        try {
            $format = $this->getConfiguration('api.formats');
        } catch (\Exception $e) {
            $format = [];
        }
        return $format;
    }

    protected function getConfig()
    {
        return $this->getServiceLocator()->get('Config')['oscar'];
    }

    public function getConfigArray()
    {
        return $this->getServiceLocator()->get('Config');
    }

    public function getVersion()
    {
        return OscarVersion::getBuild();
    }

    /**
     * @param $key
     * @return array|object
     * @throws OscarException
     */
    public function getConfiguration($key)
    {
        $config = $this->getConfig();
        if ($key) {
            $paths = explode('.', $key);
            foreach ($paths as $path) {
                if (!isset($config[$path])) {
                    throw new OscarException("Clef '$path' absente dans la configuration");
                }
                $config = $config[$path];
            }
        }
        return $config;
    }

    public function getPayementsConfig()
    {
        return $this->getOptionalConfiguration('payements', [
            'separator' => '$$',
            'persons' => '',
            'organizations' => ''
        ]);
    }

    /**
     * @return string
     */
    public function getLoggerFilePath(): string
    {
        return $this->getOptionalConfiguration('log_path', __DIR__ . '/../../../../../logs/oscar.log');
    }

    /**
     * @return string
     */
    public function getLoggerLevel(): int
    {
        return $this->getOptionalConfiguration('log_level', Logger::WARNING);
    }


    /**
     * @param $key
     * @param null $defaultValue
     * @return array|null|object
     */
    public function getOptionalConfiguration($key, $defaultValue = null)
    {
        $config = $this->getConfig();
        if ($key) {
            $paths = explode('.', $key);
            foreach ($paths as $path) {
                if (!isset($config[$path])) {
                    return $defaultValue;
                }
                $config = $config[$path];
            }
        }
        return $config;
    }

    /**
     * @return IHtmlToPdfFormatter
     * @throws OscarException
     * @throws \ReflectionException
     */
    public function getHtmlToPdfMethod()
    {
        $config = $this->getConfiguration('htmltopdfrenderer');
        $class = $config['class'];
        $arguments = $config['arguments'];
        $instance = new \ReflectionClass($class);
        return $instance->newInstanceArgs($arguments);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return string[]
     */
    public function getNumerotationKeys()
    {
        return $this->getEditableConfKey('numerotation', []);
    }

    protected function getYamlConfigPath()
    {
        $dir = realpath(__DIR__ . '/../../../../../config/autoload/');
        $file = $dir . '/oscar-editable.yml';

        if (!file_exists($file)) {
            if (!is_writeable($dir)) {
                throw new OscarException("Impossible d'écrire la configuration dans le dossier $dir");
            }
        } else if (!is_writeable($file)) {
            throw new OscarException("Impossible d'écrire le fichier $file");
        }
        return $file;
    }

    protected function getEditableConfRoot()
    {
        $path = $this->getYamlConfigPath();
        if (file_exists($path)) {
            $parser = new Parser();
            return $parser->parse(file_get_contents($path));
        } else {
            return [];
        }
    }

    public function saveEditableConfKey($key, $value)
    {
        $conf = $this->getEditableConfRoot();
        $conf[$key] = $value;
        $writer = new Dumper();
        file_put_contents($this->getYamlConfigPath(), $writer->dump($conf));
    }

    public function getEditableConfKey($key, $default = null)
    {
        $conf = $this->getEditableConfRoot();
        if (array_key_exists($key, $conf)) {
            return $conf[$key];
        } else {
            return $default;
        }
    }

    public function getOrganizationLeaderRole()
    {
        return $this->getEditableConfKey(self::organization_leader_role, []);
    }

    public function setOrganizationLeaderRole($value)
    {
        $this->saveEditableConfKey(self::organization_leader_role, $value);
    }

    public function getNumerotationEditable()
    {
        return $this->getEditableConfKey(self::allow_numerotation_custom, false);
    }

    public function setNumerotationEditable($boolean)
    {
        $this->saveEditableConfKey(self::allow_numerotation_custom, $boolean);
    }

    public function getValidationPFI()
    {
        return $this->getConfiguration('validation.pfi');
    }

    public function getTheme()
    {
        $global = $this->getConfiguration('theme');
        return $this->getEditableConfKey('theme', $global);
    }

    // --- PARAMETRES de RELANCE

    // DECLARANT
    // RELANCE 1
    public function getDeclarersRelance1()
    {
        return $this->getEditableConfKey('declarersRelance1', '');
    }

    public function setDeclarersRelance1($value)
    {
        return $this->saveEditableConfKey('declarersRelance1', $value);
    }

    public function getDeclarersRelanceJour1()
    {
        return $this->getEditableConfKey('declarersRelanceJour1', 1);
    }

    public function setDeclarersRelanceJour1($value)
    {
        return $this->saveEditableConfKey('declarersRelanceJour1', $value);
    }

    // RELANCE 2
    public function getDeclarersRelance2()
    {
        return $this->getEditableConfKey('declarersRelance2', '');
    }

    public function setDeclarersRelance2($value)
    {
        return $this->saveEditableConfKey('declarersRelance2', $value);
    }

    public function getDeclarersRelanceJour2()
    {
        return $this->getEditableConfKey('declarersRelanceJour2', 5);
    }

    public function setDeclarersRelanceJour2($value)
    {
        return $this->saveEditableConfKey('declarersRelanceJour2', $value);
    }

    // VALIDATORS
    // RELANCE 1
    public function getvalidatorsRelance1()
    {
        return $this->getEditableConfKey('validatorsRelance1', '');
    }

    public function setvalidatorsRelance1($value)
    {
        return $this->saveEditableConfKey('validatorsRelance1', $value);
    }

    public function getvalidatorsRelanceJour1()
    {
        return $this->getEditableConfKey('validatorsRelanceJour1', 1);
    }

    public function setvalidatorsRelanceJour1($value)
    {
        return $this->saveEditableConfKey('validatorsRelanceJour1', $value);
    }

    // RELANCE 2
    public function getvalidatorsRelance2()
    {
        return $this->getEditableConfKey('validatorsRelance2', '');
    }

    public function setvalidatorsRelance2($value)
    {
        return $this->saveEditableConfKey('validatorsRelance2', $value);
    }

    public function getvalidatorsRelanceJour2()
    {
        return $this->getEditableConfKey('validatorsRelanceJour2', 5);
    }

    public function setvalidatorsRelanceJour2($value)
    {
        return $this->saveEditableConfKey('validatorsRelanceJour2', $value);
    }


    public function setTheme($theme)
    {
        if (in_array($theme, $this->getConfiguration('themes'))) {
            $this->saveEditableConfKey('theme', $theme);
        } else {
            throw new OscarException("Le thème '$theme' n'existe pas");
        }
    }

    public function getTimesheetExcel()
    {
        return $this->getEditableConfKey('timesheet_excel', false);
    }

    public function setTimesheetExcel($bool)
    {
        $this->saveEditableConfKey('timesheet_excel', (boolean)$bool ? true : false);
    }

    public function getTimesheetPreview()
    {
        return $this->getEditableConfKey('timesheet_preview', false);
    }

    public function setTimesheetPreview($bool)
    {
        $this->saveEditableConfKey('timesheet_preview', (boolean)$bool ? true : false);
    }

    /**
     * Retourne l'emplacement où sont stoqués les documents publiques.
     * @return string
     */
    public function getDocumentPublicPath()
    {
        static $publicdoclocation;
        if ($publicdoclocation == null) {
            $conf = $this->getConfiguration('paths.document_admin_oscar');
            if (!file_exists($conf) || !is_writable($conf)) {
                throw new OscarException("L'emplacement des documents publiques n'est pas un dossier accessible en écriture");
            }
            $publicdoclocation = $conf . '/';
        }
        return $publicdoclocation;
    }


    public function getExportSeparator()
    {
        return $this->getEditableConfKey('export_format', '|');
    }

    public function getExportComputedFields()
    {
        return $this->getConfiguration('export.computedFields', []);
    }

    public function getEstimatedSpentActivityTemplate()
    {
        return $this->getConfiguration('estimated_spent_activity_template');
    }

    public function getGearmanHost()
    {
        return $this->getConfiguration('gearman-job-server-host');
    }

    public function setExportSeparator($string)
    {
        $this->saveEditableConfKey('export_format', $string);
    }

    public function getExportDateFormat()
    {
        return $this->getEditableConfKey('export_dateformat', 'y-m-d');
    }

    public function setExportDateFormat($string)
    {
        $this->saveEditableConfKey('export_dateformat', $string);
    }

    public function getDocumentUseVersionInName(): bool
    {
        return $this->getEditableConfKey(self::document_use_version_in_name, false);
    }

    public function setDocumentUseVersionInName(bool $bool)
    {
        return $this->saveEditableConfKey(self::document_use_version_in_name, $bool);
    }

    public function getSpentAccountFilter()
    {
        return $this->getEditableConfKey(self::spents_account_filter, []);
    }

    public function setSpentAccountFilter($stringArray)
    {
        $extract = new DataStringArray();
        $data = $extract->extract($stringArray);
        $this->saveEditableConfKey(self::spents_account_filter, $data);
    }

    public function getActivityRequestLimit()
    {
        return $this->getEditableConfKey(self::activity_request_limit, 5);
    }

    public function setActivityRequestLimit(int $value)
    {
        $this->saveEditableConfKey(self::activity_request_limit, $value);
    }

    public function getMasses()
    {
        return $this->getConfiguration('spenttypeannexes');
    }

    public function getPcruEnabled()
    {
        return $this->getEditableConfKey('pcru_enabled', false);
    }

    public function getPcruFtpInfos()
    {
        if (!$this->getPcruEnabled()) {
            throw new OscarException("Le module PCRU n'est pas activé");
        }
        return [
            'host' => $this->getEditableConfKey('pcru_host'),
            'port' => $this->getEditableConfKey('pcru_port'),
            'user' => $this->getEditableConfKey('pcru_user'),
            'pass' => $this->getEditableConfKey('pcru_pass'),
            'ssh' => $this->getEditableConfKey('pcru_ssh'),
            'timeout' => $this->getEditableConfKey('pcru_timeout', 15),
        ];
    }

    /**
     * @return bool
     */
    public function getAutoUpdateSpent()
    {
        return $this->getOptionalConfiguration("autorefreshspents", false);
    }
}