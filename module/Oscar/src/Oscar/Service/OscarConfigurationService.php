<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 22/02/19
 * Time: 11:26
 */

namespace Oscar\Service;


use Oscar\Exception\OscarException;
use Oscar\OscarVersion;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

class OscarConfigurationService implements ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    const allow_numerotation_custom     = 'allow_numerotation_custom';
    const organization_leader_role      = 'organization_leader_role';
    const theme = 'theme';

    protected function getConfig(){
        return $this->getServiceLocator()->get('Config')['oscar'];
    }

    public function getConfigArray(){
        return $this->getServiceLocator()->get('Config');
    }

    public function getVersion(){
        return OscarVersion::getBuild();
    }

    /**
     * @param $key
     * @return array|object
     * @throws OscarException
     */
    public function getConfiguration($key){
        $config = $this->getConfig();
        if( $key ){
            $paths = explode('.', $key);
            foreach ($paths as $path) {
                if( !isset($config[$path]) ){
                    throw new OscarException("Clef '$path' absente dans la configuration");
                }
                $config = $config[$path];
            }
        }
        return $config;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return array|null|object
     */
    public function getOptionalConfiguration($key, $defaultValue = null){
        $config = $this->getConfig();
        if( $key ){
            $paths = explode('.', $key);
            foreach ($paths as $path) {
                if( !isset($config[$path]) ){
                    return $defaultValue;
                }
                $config = $config[$path];
            }
        }
        return $config;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return string[]
     */
    public function getNumerotationKeys(){
        return $this->getEditableConfKey('numerotation', []);
    }

    protected function getYamlConfigPath(){
        $dir = realpath(__DIR__.'/../../../../../config/autoload/');
        $file = $dir.'/oscar-editable.yml';

        if( !file_exists($file) ){
            if( !is_writeable($dir) ){
                throw new OscarException("Impossible d'écrire la configuration dans le dossier $dir");
            }
        }
        else if (!is_writeable($file)) {
            throw new OscarException("Impossible d'écrire le fichier $file");
        }
        return $file;
    }

    protected function getEditableConfRoot(){
        $path = $this->getYamlConfigPath();
        if( file_exists($path) ){
            $parser = new Parser();
            return $parser->parse(file_get_contents($path));
        } else {
            return [];
        }
    }

    public function saveEditableConfKey($key, $value){
        $conf = $this->getEditableConfRoot();
        $conf[$key] = $value;
        $writer = new Dumper();
        file_put_contents($this->getYamlConfigPath(), $writer->dump($conf));
    }

    public function getEditableConfKey($key, $default = null){
        $conf = $this->getEditableConfRoot();
        if( array_key_exists($key, $conf) ){
            return $conf[$key];
        } else {
            return $default;
        }
    }

    public function getOrganizationLeaderRole(){
        return $this->getEditableConfKey(self::organization_leader_role, []);
    }

    public function setOrganizationLeaderRole($value){
        $this->saveEditableConfKey(self::organization_leader_role, $value);
    }

    public function getNumerotationEditable(){
        return $this->getEditableConfKey(self::allow_numerotation_custom, false);
    }

    public function setNumerotationEditable( $boolean ){
        $this->saveEditableConfKey(self::allow_numerotation_custom, $boolean);
    }

    public function getValidationPFI(){
        return $this->getConfiguration('validation.pfi');
    }

    public function getTheme(){
        $global = $this->getConfiguration('theme');
        return $this->getEditableConfKey('theme', $global);
    }

    public function setTheme( $theme ){
        if( in_array($theme, $this->getConfiguration('themes')) ){
            $this->saveEditableConfKey('theme', $theme);
        } else {
            throw new OscarException("Le thème '$theme' n'existe pas");
        }
    }

    public function getTimesheetExcel(){
        return $this->getEditableConfKey('timesheet_excel', false);
    }

    public function setTimesheetExcel( $bool ){
        $this->saveEditableConfKey('timesheet_excel', (boolean)$bool ? true : false );
    }

    public function getTimesheetPreview(){
        return $this->getEditableConfKey('timesheet_preview', false);
    }

    public function setTimesheetPreview( $bool ){
        $this->saveEditableConfKey('timesheet_preview', (boolean)$bool ? true : false );
    }


    public function getExportSeparator(){
        return $this->getEditableConfKey('export_format', '|');
    }

    public function getExportComputedFields(){
        return $this->getConfiguration('export.computedFields', []);
    }

    public function setExportSeparator( $string ){
        $this->saveEditableConfKey('export_format', $string);
    }

    public function getExportDateFormat(){
        return $this->getEditableConfKey('export_dateformat', 'y-m-d');
    }

    public function setExportDateFormat( $string ){
        $this->saveEditableConfKey('export_dateformat', $string);
    }
}