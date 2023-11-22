<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 22/02/19
 * Time: 11:26
 */

namespace Oscar\Service;


use Monolog\Logger;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\TabDocument;
use Oscar\Exception\OscarException;
use Oscar\Formatter\File\IHtmlToPdfFormatter;
use Oscar\Import\Data\DataStringArray;
use Oscar\OscarVersion;
use Oscar\Utils\FileSystemUtils;
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
    const document_location = 'document_location';
    const declarers_white_list = 'declarers_white_list';
    const auth_person_normalize = 'authPersonNormalize';
    const pfi_strict = 'pfi_strict';
    const pfi_strict_format = 'pfi_strict_format';
    const allow_node_selection = 'allow_node_selection';
    const empty_project_require_validation = 'empty_project_require_validation';
    const financial_label = 'financial_label';
    const financial_description = 'financial_description';
    const spent_effective_clause = 'spent_effective_clause';
    const spent_predicted_clause = 'spent_predicted_clause';


    const theme = 'theme';

    public function emptyProjectRequireValidation(): bool
    {
        return $this->getConfiguration(self::empty_project_require_validation);
    }

    /**
     * @return bool
     */
    public function isPfiStrict(): bool
    {
        $new = $this->getEditableConfKey(self::pfi_strict, null);

        if ($new === null) {
            return true;
        }
        return $new;
    }

    /**
     * @param bool $strict
     * @throws OscarException
     */
    public function setStrict(bool $strict): void
    {
        $this->saveEditableConfKey(self::pfi_strict, $strict);
    }

    /**
     * @return string
     */
    public function getPfiRegex(): string
    {
        return $this->getEditableConfKey(self::pfi_strict_format, "");
    }

    public function isAllowNodeSelection(): bool
    {
        return $this->getEditableConfKey(self::allow_node_selection, "1") == "1";
    }

    public function setAllowNodeSelection(bool $allow): void
    {
        $this->saveEditableConfKey(self::allow_node_selection, $allow === true ? "1" : "0");
    }


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
        return $this->getOptionalConfiguration(
            'payements',
            [
                'separator' => '$$',
                'persons' => '',
                'organizations' => ''
            ]
        );
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
        return $this->getOptionalConfiguration('log_level', Logger::INFO);
    }

    public function useDeclarersWhiteList(): bool
    {
        return $this->getEditableConfKey(self::declarers_white_list, true);
    }

    public function setUseDeclarerWhiteList(bool $use): void
    {
        $this->saveEditableConfKey(self::declarers_white_list, $use);
    }

    /**
     * Retourne la valeur à testé dans la table "spentline" sur le champ "rldnr" pour isoler
     * les dépenses effectives.
     *
     * @return string
     */
    public function getSpentEffectiveClauseValue() :string
    {
        return $this->getEditableConfKey(self::spent_effective_clause, '9A');
    }

    /**
     * Retourne la valeur à testé dans la table "spentline" sur le champ "rldnr" pour isoler
     * les dépenses prévues.
     *
     * @return string
     */
    public function getSpentPredictedClauseValue() :string
    {
        return $this->getEditableConfKey(self::spent_predicted_clause, '9B');
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
        } else {
            if (!is_writeable($file)) {
                throw new OscarException("Impossible d'écrire le fichier $file");
            }
        }
        return $file;
    }

    /**
     * Chargement de la configuration depuis le fichier YAML 'oscar-editable.yml'
     *
     * @return array|mixed|\stdClass|\Symfony\Component\Yaml\Tag\TaggedValue
     * @throws OscarException
     */
    protected function getEditableConfRoot() :array
    {
        $path = $this->getYamlConfigPath();
        if (file_exists($path)) {
            $parser = new Parser();
            $parsed = $parser->parse(file_get_contents($path));
            if ($parsed) {
                return $parsed;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     * @throws OscarException
     */
    public function saveEditableConfKey($key, $value) :self
    {
        $conf = $this->getEditableConfRoot();
        $conf[$key] = $value;
        $writer = new Dumper();
        file_put_contents($this->getYamlConfigPath(), $writer->dump($conf));
        return $this;
    }

    public function getEditableConfKey($key, $default = null)
    {
        $conf = $this->getEditableConfRoot();
        if ($conf == null) {
            $conf = [];
        }
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
        $reg = $this->getEditableConfKey(self::pfi_strict_format);
        // On test si la nouvelle configuration est utilisée
        if ($reg == null) {
            return $this->getConfiguration('validation.pfi');
        }
        return $reg;
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

    public function getDeclarersRelanceConflitMessage(): string
    {
        return $this->getEditableConfKey('declarersRelanceConflitMessage', '');
    }

    public function getDeclarersRelanceConflitJour(): int
    {
        return $this->getEditableConfKey('declarersRelanceConflitJour', 1);
    }

    public function setDeclarersRelanceConflitMessage(string $message): self
    {
        return $this->saveEditableConfKey('declarersRelanceConflitMessage', $message);
    }

    public function setDeclarersRelanceConflitJour(int $days): self
    {
        return $this->saveEditableConfKey('declarersRelanceConflitJour', $days);
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

    public function getHighDelayRelance()
    {
        return $this->getEditableConfKey('highdelayRelance', "");
    }

    public function setHighDelayRelance($value)
    {
        return $this->saveEditableConfKey('highdelayRelance', $value);
    }

    public function getHighDelayRelanceJour(): int
    {
        return $this->getEditableConfKey('highdelayRelanceJour', 3);
    }

    public function setHighDelayRelanceJour(int $value)
    {
        return $this->saveEditableConfKey('highdelayRelanceJour', $value);
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

    public function getTimesheetTemplateActivityPeriod() :string
    {
        return $this->getConfiguration('timesheet_activity_synthesis_template');
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// MAILS D'envoi/rejet
    public function getEmailRejectBody(): string
    {
        return $this->getOptionalConfiguration(
            'email_reject_body',
            "Bonjour {PERSON},\r\nVotre déclaration pour la période {PERIOD} a été rejetée.\r\nMerci de corriger votre déclaration"
        );
    }

    public function getEmailRejectSubject(): string
    {
        return $this->getOptionalConfiguration('email_reject_subject', "Déclaration rejetée");
    }

    public function getEmailToValidatetBody(): string
    {
        return $this->getOptionalConfiguration(
            'email_tovalidate_body',
            "Bonjour,\r\nUne déclaration de {PERSON} pour la période {PERIOD} attend votre validation.\r\nMerci"
        );
    }

    public function getEmailToValidateSubject(): string
    {
        return $this->getOptionalConfiguration('email_tovalidate_subject', "Déclaration à valider");
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
                throw new OscarException(
                    "L'emplacement des documents publiques n'est pas un dossier accessible en écriture"
                );
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
        return $this->getEditableConfKey('export_dateformat', 'Y-m-d');
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

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// PCRU
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    const PCRU_UNITE_ROLE_DEFAULT = 'Laboratoire';
    const PCRU_PARTNER_ROLE_DEFAULT = 'Partenaire';
    const PCRU_INCHARGE_ROLE_DEFAULT = 'Responsable scientifique';

    /**
     * @return bool
     */
    public function getPcruEnabled()
    {
        return $this->getEditableConfKey('pcru_enabled', false);
    }


    public function getPcruUnitRoles(): array
    {
        return $this->getEditableConfKey('pcru_unit_roles', [self::PCRU_UNITE_ROLE_DEFAULT]);
    }

    /**
     * @return string
     */
    public function getPcruPartnerRoles(): array
    {
        return $this->getEditableConfKey('pcru_partner_roles', [self::PCRU_PARTNER_ROLE_DEFAULT]);
    }

    /**
     * @return string
     */
    public function getPcruInChargeRole(): string
    {
        return $roleRSToFind = $this->getEditableConfKey('pcru_incharge_role', self::PCRU_INCHARGE_ROLE_DEFAULT);
    }

    /**
     * @return string
     */
    public function getPcruContractType(): string
    {
        //->getOptionalConfiguration('pcru_contrat_type', "Contrat Version Définitive Signée");
        return $roleRSToFind = $this->getEditableConfKey('pcru_contract_type', "Contrat Version Définitive Signée");
    }

    /**
     * @return array
     * @throws OscarException
     */
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

    public function getAuthPersonNormalize(): bool
    {
        return $this->getConfiguration(self::auth_person_normalize, false);
    }

    public function getFinancialLabel(): string
    {
        return $this->getEditableConfKey(self::financial_label, 'PFI');
    }

    public function getFinancialDescription(): string
    {
        return $this->getEditableConfKey(self::financial_description, 'Numéro PFI');
    }

    public function setFinancialLabel(string $label)
    {
        return $this->saveEditableConfKey(self::financial_label, $label);
    }

    public function setFinancialDescription(string $description)
    {
        return $this->saveEditableConfKey(self::financial_description, $description);
    }

    /**
     * Retourne le dossier racine PCRU.
     *
     * @return string
     * @throws OscarException
     */
    public function getPcruRootDirectory(): string
    {
        static $pcru_root = null;
        if ($pcru_root === null) {
            // Test de la racine PCRU
            $path = realpath($this->getConfiguration('pcru.files_path'));
            if (!file_exists($path)) {
                throw new OscarException(
                    "Erreur de configuration PCRU : Le dossier de traitement temporaire n'existe pas"
                );
            }
            if (!is_dir($path)) {
                throw new OscarException(
                    "Erreur de configuration PCRU : Le dossier de traitement temporaire doit être un dossier"
                );
            }
            if (!is_writable($path)) {
                throw new OscarException(
                    "Erreur de configuration PCRU : Le dossier de traitement doit être accessible en écriture"
                );
            }
            $pcru_root = $path;
        }
        return $pcru_root;
    }

    /**
     * Retourne le dossier où sont archivés les fichiers avant l'envoi PCRU.
     *
     * @return string
     * @throws OscarException
     */
    public function getPcruDirectoryForUpload(): string
    {
        static $pcru_pool = null;
        if ($pcru_pool === null) {
            // Test de la racine PCRU
            $path = $this->getPcruRootDirectory() . DIRECTORY_SEPARATOR . $this->getConfiguration('pcru.pool_current');

            if (!file_exists($path)) {
                if (!@mkdir($path)) {
                    throw new OscarException("Erreur de configuration PCRU : Impossible de créer le dossier d'envoi");
                }
            }

            if (!is_dir($path)) {
                throw new OscarException("Erreur de configuration PCRU : Le dossier d'envoi doit être un dossier");
            }

            if (!is_writable($path)) {
                throw new OscarException(
                    "Erreur de configuration PCRU : Le dossier d'envoi doit être accessible en écriture"
                );
            }

            $pcru_pool = $path;
        }
        return $pcru_pool;
    }

    public function getPcruDirectoryForUploadEffective(): string
    {
        return $this->getPcruRootDirectory()
            . DIRECTORY_SEPARATOR
            . $this->getConfiguration('pcru.pool_effective');
    }

    public function getPcruPoolLockFile(): string
    {
        return $this->getPcruDirectoryForUpload()
            . DIRECTORY_SEPARATOR
            . $this->getConfiguration('pcru.pool_lock');
    }

    public function getPcruLogPoolFile(): string
    {
        return $this->getPcruDirectoryForUpload()
            . DIRECTORY_SEPARATOR
            . $this->getConfiguration('pcru.pool_log');
    }

    /**
     * @param bool $withPath
     * @return string
     * @throws OscarException
     */
    public function getPcruContratFile($withPath = true): string
    {
        return $withPath ?
            $this->getPcruDirectoryForUpload()
            . DIRECTORY_SEPARATOR
            . $this->getConfiguration('pcru.filename_contrats')
            :
            $this->getConfiguration('pcru.filename_contrats');
    }

    public function getPcruSendCsvOkFile() :string
    {
        return $this->getConfiguration('pcru.filename_csv_ok');
    }

    public function getPcruSendPdfOkFile() :string
    {
        return $this->getConfiguration('pcru.filename_pdf_ok');
    }

    /**
     * @param bool $withPath
     * @return string
     * @throws OscarException
     */
    public function getPcruPartenaireFile($withPath = true): string
    {
        return $withPath ?
            $this->getPcruDirectoryForUpload()
            . DIRECTORY_SEPARATOR
            . $this->getConfiguration('pcru.filename_partenaires')
            :
            $this->getConfiguration('pcru.filename_partenaires');
    }

    /**
     * Retourne le chemin absolue du stockage des documents sans catégorie/non-privé (Activités de recherche).
     *
     * @return string
     * @throws OscarException
     */
    public function getDocumentDropLocation(): string
    {
        static $documentDropLocation;
        if ($documentDropLocation == null) {
            $path = realpath($this->getConfiguration('paths.document_oscar'));
            try {
                FileSystemUtils::getInstance()->checkDirWritable($path);
            } catch (\Exception $e) {
                throw new OscarException(_("L'emplacement de stockage des documents est manquant/inaccessible."));
            }
            $documentDropLocation = $path;
        }
        return $documentDropLocation;
    }

    /**
     * Retourne le chemin absolue du stockage des documents privés (Activités de recherche)
     *
     * @return string
     * @throws OscarException
     */
    public function getDocumentPrivateLocation(): string
    {
        static $documentPrivateLocation;
        if ($documentPrivateLocation == null) {
            $name = 'private';
            $path = $this->getDocumentDropLocation() . DIRECTORY_SEPARATOR . $name;
            try {
                FileSystemUtils::getInstance()->mkdir($path);
            } catch (\Exception $e) {
                throw new OscarException(_("L'emplacement de stockage des documents privé est inaccessible."));
            }
            $documentPrivateLocation = $path;
        }
        return $documentPrivateLocation;
    }


    public function getDocumentTabLocation(TabDocument $tab): string
    {
        static $documentTabLocation;
        if ($documentTabLocation == null) {
            $documentTabLocation = [];
        }

        $name = sprintf('tab_%s', $tab->getId());
        if (!array_key_exists($name, $documentTabLocation)) {
            $path = $this->getDocumentDropLocation() . DIRECTORY_SEPARATOR . $name;
            try {
                FileSystemUtils::getInstance()->mkdir($path);
            } catch (\Exception $e) {
                throw new OscarException(
                    _(
                        sprintf(
                            "L'emplacement de stockage des documents de l'onglet '%s' est inaccessible.",
                            $tab->getLabel()
                        )
                    )
                );
            }
            $documentTabLocation[$name] = $path;
        }
        return $documentTabLocation[$name];
    }

    public function getDocumentRealpath(ContractDocument $document): string
    {
        $basePath = $this->getDocumentDropLocation();
        return $basePath . DIRECTORY_SEPARATOR . $document->generatePath();
    }


}