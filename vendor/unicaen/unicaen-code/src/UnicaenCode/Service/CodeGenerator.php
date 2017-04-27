<?php
namespace UnicaenCode\Service;

use Exception;
use UnicaenCode\Service\Traits\ConfigAwareTrait;
use UnicaenCode\Util;
use Zend\Stdlib\ArrayUtils;

/**
 *
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class CodeGenerator
{
    use ConfigAwareTrait;

    /**
     * template
     *
     * @var string
     */
    protected $template;

    /**
     * params
     *
     * @var array
     */
    protected $params;

    /**
     * @var string
     */
    protected $outputDir;



    function getTemplate()
    {
        return $this->template;
    }



    function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }



    function getParams()
    {
        return $this->params;
    }



    function setParams($params)
    {
        $this->params = $params;

        return $this;
    }



    /**
     * @return string
     */
    public function getOutputDir()
    {
        if (!$this->outputDir) return $this->getServiceConfig()->getGeneratorOutputDir(); // par défaut celui de la config
        return $this->outputDir;
    }



    /**
     * @param string $outputDir
     *
     * @return CodeGenerator
     */
    public function setOutputDir($outputDir)
    {
        $this->outputDir = $outputDir;

        return $this;
    }



    /**
     *
     * @throws Exception
     */
    public function generate()
    {
        if (!$this->template) {
            throw new Exception('Template non fourni');
        }
        if (!$this->params) {
            throw new Exception('params non fournis');
        }

        $code = $this->getTemplateString();

        foreach ($this->params as $param => $value) {
            $code = $this->applyParam($code, $param, $value);
        }

        $code = trim($code); // éviter les espaces inutiles en début et fin de fichier

        /* On débarasse le code de ses caractères inutiles !! */
        $code = explode(PHP_EOL, $code);
        foreach ($code as $i => $line) {
            if (trim($line)) {
                $code[$i] = rtrim($line);
            } else {
                $code[$i] = '';
            }
        }
        $code = implode(PHP_EOL, $code);

        return $code;
    }



    protected function applyParam($code, $param, $value)
    {
        // Si c'est un tableau alors on concatène en string...
        if (is_array($param)) {
            $param = implode('', $param);
        }

        // application des paramètres simples
        $code = str_replace('<' . $param . '>', $value, $code);

        // Gestion des conditions
        $if = '<if ' . $param . '>';
        while (false !== ($begin = strpos($code, $if))) {
            $endIf = '<endif ' . $param . '>';

            $end = strpos($code, $endIf, $begin);

            if ($value) {
                $ifCode = ltrim(substr($code, $begin + strlen($if), $end - $begin - strlen($if)));
            } else {
                $ifCode = '';
            }

            $end += strlen($endIf);

            if (isset($code[$begin - 1]) && isset($code[$end])) {
                $b = trim($code[$begin - 1]);
                $e = trim($code[$end]);
                // ATTENTION : il n'y pas de support des retours chariot Windows (\n\r) car ils font 2 caractères ! ! !
                if ($b === '' && $e === '') $end++; // pour éviter les retours chariot ou espaces inutiles
            }

            $code = substr($code, 0, $begin) . $ifCode . substr($code, $end);
        }

        // restitution
        return $code;
    }



    /**
     * Génère le fichier et l'écrit directement sur le disque dur, dans le répertoire "outputDir"
     *
     * @param $filename
     *
     * @return $this
     * @throws Exception
     */
    public function generateToFile($filename)
    {
        $outputDir = $this->getOutputDir();

        if (!is_dir($outputDir)) {
            mkdir($outputDir);
            chmod($outputDir, 0777);
        }
        if (substr($outputDir, -1) !== '/') {
            $outputDir .= '/';
        }

        $parts = explode('/', $filename);
        array_pop($parts); // retire le nom du fichier
        $dir = '';
        foreach ($parts as $part) {
            if (!is_dir($outputDir . ($dir .= "/$part"))) {
                mkdir($outputDir . $dir);
                chmod($outputDir . $dir, 0777);
            }
        }


        file_put_contents($outputDir . $filename, $this->generate());
        chmod($outputDir . $filename, 0777);

        return $this;
    }



    public function generateToHtml($title)
    {
        $id = uniqid('bloc_code_');

        ?>
        <a role="button" data-toggle="collapse" href="#<?php echo $id ?>" aria-expanded="false"
           aria-controls="collapseExample">
            <?php echo $title ?>
        </a><br/>
        <div class="collapse" id="<?php echo $id ?>">
            <?php Util::highlight($this->generate(), 'php', true, ['show-line-numbers' => true]); ?>
        </div>
        <?php
        return $this;
    }



    public function generateFiles(array $params)
    {
        $keys = ['Class', 'Trait', 'Interface'];

        foreach ($keys as $key) {
            if (isset($params[$key])) {
                $this->generateFile($params[$key]);
            }
        }

        return $this;
    }



    public function generateFile($params, $writeToDisk = true)
    {
        $this->setTemplate($params['template']);
        $this->setParams($params);
        $this->generateToHtml($params['filename']);
        if ($writeToDisk) {
            $this->generateToFile($params['filename']);
        }

        return $this;
    }



    public function generateViewHelperParams(array $options, array $params = [])
    {
        $defaultOptions = [
            'type' => 'ViewHelper',
        ];
        $options        = ArrayUtils::merge($defaultOptions, $options);

        return $this->generateParams($options, $params);
    }



    /**
     * @param array $options
     * @param array $params
     *
     * Options = [
     *      $traitInterface = {Trait, Interface}
     *      $classname
     *      $rootNamespace  = namespace par défaut des entités!!
     * ]
     */
    public function generateEntityParams(array $options, array $params = [])
    {
        $tis = [];

        $defaultOptions = [
            'generateTrait'     => false,
            'generateInterface' => false,
            'rootNamespace'     => '',
        ];
        extract(ArrayUtils::merge($defaultOptions, $options));

        if ($generateTrait) $tis[] = 'Trait';
        if ($generateInterface) $tis[] = 'Interface';

        $rns = Util::truncatedClassName(Util::moduleClass($rootNamespace), $rootNamespace);
        $rns = Util::classNameToCamelCase($rns);

        $defaultParams = [];
        foreach ($tis as $traitInterface) {
            $ti = $this->generateTraitInterfaceParams('Entity', $traitInterface, $classname);

            if (0 === strpos($ti['method'], $rns)) {
                $ti['method'] = substr($ti['method'], strlen($rns));
            }
            $ti['variable'] = lcfirst($ti['method']);

            $defaultParams[$traitInterface] = $ti;
        }


        $params = ArrayUtils::merge($defaultParams, $params);

        return $params;
    }



    /**
     * @param array $options
     *
     * Options = [
     *      $type
     *      $classname
     *      $name
     *      $useHydrator
     * ]
     */
    public function generateFormParams(array $options, array $params = [])
    {
        $defaultOptions = [
            'type'        => 'Form',
            'useHydrator' => true,
        ];

        $type            = $options['type'];
        $options         = ArrayUtils::merge($defaultOptions, $options);
        $options['type'] = 'Form';

        $defaultParams = [
            'Class' => [
                'type'        => $type,
                'useHydrator' => $options['useHydrator'],
            ],
        ];

        $params        = ArrayUtils::merge($defaultParams, $params);
        $params = $this->generateParams($options, $params);


        if ('Fieldset' == $type){
            if (isset($params['Trait']['method']) && 0 === strpos($params['Trait']['method'], 'Form')){
                $params['Trait']['method'] = 'Fieldset'.substr($params['Trait']['method'], 4);
                if ('Fieldset' == substr($params['Trait']['method'], -strlen('Fieldset') )){
                    $params['Trait']['method'] = substr( $params['Trait']['method'], 0, -strlen('Fieldset'));
                }
                $params['Trait']['variable'] = lcfirst($params['Trait']['method']);
            }
            if (isset($params['Interface']['method']) && 0 === strpos($params['Interface']['method'], 'Form')){
                $params['Interface']['method'] = 'Fieldset'.substr($params['Interface']['method'], 4);
                if ('Fieldset' == substr($params['Interface']['method'], -strlen('Fieldset') )){
                    $params['Interface']['method'] = substr( $params['Interface']['method'], 0, -strlen('Fieldset'));
                }
                $params['Interface']['variable'] = lcfirst($params['Interface']['method']);
            }
        }

        return $params;
    }



    public function generateControllerParams($targetFullClass, $name, $route, $module)
    {
        $author      = Util::getAuthor();
        $classname   = Util::baseClassName($targetFullClass);
        $namespace   = Util::namespaceClass($targetFullClass);
        $wmClassname = Util::truncatedClassName($module, $targetFullClass);
        $fileName    = Util::classNameToFileName($targetFullClass);

        return compact('module', 'name', 'route', 'classname', 'author', 'namespace', 'wmClassname', 'fileName');
    }



    public function generateHydratorParams(array $options, array $params = [])
    {
        $defaultOptions = [
            'type' => 'Hydrator',
        ];
        $options        = ArrayUtils::merge($defaultOptions, $options);

        return $this->generateParams($options, $params);
    }



    public function generateServiceParams(array $options, array $params = [])
    {
        $defaultOptions = [
            'type' => 'Service',
        ];
        $options        = ArrayUtils::merge($defaultOptions, $options);

        return $this->generateParams($options, $params);
    }



    /**
     *
     * @param array $options
     * @param array $params
     *
     * Options  = [
     *      $type               Type de classe (Service, Form, etc)
     *      $classname          Nom complet de la classe
     *      $name               Nom d'accès depuis le service locator
     *      $useServiceLocator  boolean
     *      $generateTrait      boolean
     *      $generateInterface  boolean
     *      $generateConfig     boolean
     * ]
     *
     * @return array
     */
    private function generateParams(array $options, array $params = [])
    {
        $defaultOptions = [
            'type'              => '',
            'classname'         => '',
            'name'              => '',
            'useServiceLocator' => true,
            'generateTrait'     => false,
            'generateInterface' => false,
            'generateConfig'    => true,
        ];

        extract(ArrayUtils::merge($defaultOptions, $options));

        $defaultParams = [
            'Class' => [
                'namespace'         => Util::namespaceClass($classname),
                'classname'         => Util::baseClassName($classname),
                'author'            => Util::getAuthor(),
                'useServiceLocator' => $useServiceLocator,
                'filename'          => Util::classNameToFileName($classname),
                'template'          => $type,
            ],
        ];
        if ($generateTrait) {
            $defaultParams['Trait'] = $this->generateTraitInterfaceParams($type, 'Trait', $classname, $name);
        }
        if ($generateInterface) {
            $defaultParams['Interface'] = $this->generateTraitInterfaceParams($type, 'Interface', $classname, $name);
        }
        if ($generateConfig) {
            $defaultParams['Config'] = [
                'module'      => Util::moduleClass($classname),
                'name'        => lcfirst($name),
                'wmClassname' => Util::truncatedClassName(Util::moduleClass($classname), $classname),
                'filename'    => 'module.config.php',
                'template'    => $type . 'Config',
            ];
        }

        return ArrayUtils::merge($defaultParams, $params);
    }



    /**
     *
     */
    public function generateTraitInterfaceParams($type, $traitInterface, $classname, $name = null)
    {
        $namespace       = Util::namespaceClass($classname) . '\\' . ucfirst($traitInterface) . 's';
        $targetFullClass = $classname;
        $targetClass     = Util::baseClassName($classname);
        $class           = $targetClass . 'Aware' . ucfirst($traitInterface);

        $method     = Util::truncatedClassName(Util::moduleClass($classname), $classname);
        $methodBase = Util::moduleClass($method);
        $method     = Util::classNameToCamelCase($method);
        if (substr($method, -strlen($methodBase)) == $methodBase) {
            $method = substr($method, 0, -strlen($methodBase));
        }

        $variable = lcfirst($method);
        $filename = Util::classNameToFileName($namespace . '\\' . $class);
        $template = $type . 'Aware' . $traitInterface;

        return compact('namespace', 'targetFullClass', 'class', 'targetClass', 'method', 'variable', 'name', 'filename', 'template');
    }



    /**
     *
     */
    public function generateHydratorTraitParams($targetFullClass, $name, $baseNamespace)
    {
        return $this->generateParams('Hydrator', 'Trait', $targetFullClass, $name, $baseNamespace);
    }



    /**
     *
     */
    public function generateHydratorInterfaceParams($targetFullClass, $name, $baseNamespace)
    {
        return $this->generateParams('Hydrator', 'Interface', $targetFullClass, $name, $baseNamespace);
    }




    private function getTemplateString()
    {
        $templateDirs = $this->getServiceConfig()->getTemplateDirs();
        $templateFile = null;
        foreach ($templateDirs as $templateDir) {
            $templateFile = $templateDir . '/' . $this->getTemplate() . '.php';
            if (file_exists($templateFile)) break;
        }

        if (!$templateFile) {
            throw new Exception('Le modèle ' . $this->getTemplate() . ' n\'a pas été trouvé dans les répertoires de modèles d\'UnicaenCode');
        }

        return file_get_contents($templateFile);
    }
}
