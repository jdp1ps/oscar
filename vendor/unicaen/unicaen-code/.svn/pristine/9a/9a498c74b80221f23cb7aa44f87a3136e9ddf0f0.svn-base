<?php

namespace UnicaenCode;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\View\Helper\TagViewHelper;
use UnicaenCode\Service\Config;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorInterface;


/**
 *
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class Util
{
    /**
     * @var ServiceLocatorInterface
     */
    private static $serviceLocator;



    public static function sqlLog($display = true)
    {
        if (self::getEntityManager()) {
            $sqlLogger          = new EchoSQLLogger();
            $sqlLogger->display = $display;
            self::getEntityManager()->getConfiguration()->setSQLLogger($sqlLogger);
        }
    }



    public static function sqlCount($display = false)
    {
        var_dump('Nombre de requêtes SQL exécutées : ' . count(EchoSQLLogger::$queries));
        if ($display) {
            foreach (EchoSQLLogger::$queries as $query) {
                Util::highlight($query['sql'], 'sql', true, ['parameters' => $query['params']]);
            }
        }
    }



    /**
     * @param string|QueryBuilder $sql
     */
    public static function sqlDump($sql)
    {
        self::highlight($sql, 'sql');
    }



    public static function phpDump($php)
    {
        self::highlight($php, 'php');
    }



    public static function javascriptDump($javascript)
    {
        self::highlight($javascript, 'jquery');
    }



    public static function cssDump($css)
    {
        self::highlight($css, 'css');
    }



    public static function htmlDump($html)
    {
        self::highlight($html, 'html5');
    }



    public static function xmlDump($xml)
    {
        self::highlight($xml, 'xml');
    }



    public static function varDump($variable)
    {
        var_dump(self::prepareDump($variable));
    }



    public static function prepareDump($variable)
    {
        if (is_array($variable)) {
            foreach ($variable as $k => $v) {
                $variable[$k] = self::prepareDump($v);
            }
        } elseif ($variable instanceof \Doctrine\ORM\PersistentCollection) {
            $result = [];
            foreach ($variable as $k => $v) {
                $result[$k] = self::prepareDump($v);
            }
            $variable = $result;
        } elseif (is_object($variable)) {
            if (method_exists($variable, 'getId')) {
                $variable = 'OBJET : ' . get_class($variable) . ';ID=' . $variable->getId() . ';' . $variable;
            } else {
                $variable = 'OBJET : ' . get_class($variable) . ';' . $variable;
            }
        }

        return $variable;
    }



    private static function normalizeBacktraceLine( $data )
    {
        $line = [];

        $class    = isset($data['class']) ? $data['class'] : null;
        $type     = isset($data['type']) ? $data['type'] : null;
        $function = isset($data['function']) ? $data['function'] : null;
        $file     = isset($data['file']) ? $data['file'] : null;
        $line     = isset($data['line']) ? $data['line'] : null;
        $oargs     = isset($data['args']) ? $data['args'] : null;

        if (0 === strpos($file, getcwd().'/')){
            $file = str_replace( getcwd().'/', '', $file );
        }

        if (is_array($oargs)){
            $args = [];
            foreach( $oargs as $index => $arg ){
                if (is_object($arg)){
                    $arg = get_class($arg);
                }elseif(is_array($arg)){
                    $arg = 'Array';
                }else{
                    $arg = var_export( $arg, true );
                }
                $args[] = $arg;
            }
            $args = ' ('.implode( ', ', $args ).')';
        }else{
            $args = $oargs;
        }


        $inVendor = 0 === strpos($file, 'vendor');

        return compact('class', 'type', 'function', 'args', 'file', 'line', 'inVendor');
    }



    /**
     * Affiche sur la sortie HTML une trace de débogage
     */
    public static function dumpBacktrace()
    {
        $bt = debug_backtrace(0);
        foreach( $bt as $index => $bl ){
            if (isset($bl['function']) && isset($bl['class']) && $bl['class'] == __CLASS__ && $bl['function'] == 'dumpBacktrace'){
                unset($bt[$index]);
            }else{
                $bl = self::normalizeBacktraceLine($bl);
                $bt[$index] = $bl;
            }
        }

        foreach( $bt as $index => $bl ){
            if (empty($bl['file']) && isset($bt[$index+1]) ){
                $nbl = $bt[$index+1];
                $bl['inVendor'] = $nbl['inVendor'];
            }
            $bt[$index] = $bl;
        }

        $index = 0;
        ?>

        <style>
            .backtrace{
                display: block;
                padding: 9.5px;
                margin: 0px 0px 10px;
                font-size: 13px;
                line-height: 1.42857;
                color: #333;
                word-break: break-all;
                word-wrap: break-word;
                background-color: #F5F5F5;
                border: 1px solid #CCC;
                border-radius: 4px;
                font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
            }
            #navbar .backtrace {
                display: none;
            }
            .backtrace pre {
                padding:3px;font-size:8pt;background-color:white
            }
        </style>
        <div class="backtrace"><?php foreach ($bt as $bl): ?>
            <div style="white-space:nowrap<?php if ($bl['inVendor']) echo ';opacity:.5' ?>">
                <?php
                    echo $index++ . ' ' . $bl['file'];
                    if ($bl['line'])
                        echo ' <span class="badge">' . $bl['line'] . '</span>';
                ?>
            </div>
            <div style="margin-left:8em;margin-top:1px;margin-bottom:5px<?php if ($bl['inVendor']) echo ';opacity:.5' ?>">
                <?php
                    self::highlight($bl['class'].$bl['type'].$bl['function'].$bl['args'], 'php');
                ?>
            </div>
        <?php endforeach; ?>
        </div>
        <?php
    }



    public static function highlight($data, $language = 'php', $echo = true, $options = [])
    {
        $pre  = '';
        $post = '';

        if ($language === 'sql') {
            if ($data instanceof QueryBuilder || $data instanceof Query) {
                if ($data instanceof  QueryBuilder){
                    $data = $data->getQuery();
                }

                $parameters = [];
                foreach ($data->getParameters() as $id => $parameter) {
                    $pval = '';
                    if ($parameter->getValue() instanceof \DateTime) {
                        $pval = $parameter->getValue()->format('d/m/Y');
                    } elseif (is_object($parameter->getValue())) {
                        if (method_exists($parameter->getValue(), 'getId')) $pval .= '<strong>' . $parameter->getValue()->getId() . '</strong>';
                        if (method_exists($parameter->getValue(), '__toString')) $pval .= ' : ' . (string)$parameter->getValue();
                        if ($pval == '') $pval = get_class($parameter->getValue());
                    } else {
                        $pval = (string)$parameter->getValue();
                    }
                    $parameters[$parameter->getName() . ' (' . $id . ')'] = $pval;
                }
                $data = $data->getSQL();
            } elseif (isset($options['parameters'])) {
                $parameters = $options['parameters'];
            } else {
                $parameters = [];
            }
            if (!empty($parameters)) {
                $post .= '<table class="table table-bordered table-condensed" style="width:auto"><tr><th>Paramètre</th><th>Valeur</th></tr>';
                foreach ($parameters as $key => $value) {
                    $post .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
                }
                $post .= '</table>';
            }
            if (self::includeSqlFormatter()) {
                $data = \SqlFormatter::format($data, false);
            }
        } elseif ($language === 'php') {
            if (!is_string($data)) $data = var_export($data, true);
        }

        if (self::includeGeshi()) {
            $geshi = new \GeSHi($data, $language);
            $geshi->set_header_type(GESHI_HEADER_PRE);
            if (isset($options['show-line-numbers']) && $options['show-line-numbers']) {
                $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
                $geshi->set_line_style('background: white');
            }
            if ($echo) {
                echo $pre . $geshi->parse_code() . $post;
            } else {
                return $pre . $geshi->parse_code() . $post;
            }
        } else {
            if ($echo) {
                echo $pre . $data . $post;
            } else {
                return $pre . $data . $post;
            }
        }
    }



    /**
     * retourne le strict nom de classe de la classe (sans son namespace)
     *
     * @param string $classname nom de classe, avec son namespace
     *
     * @return string
     */
    public static function baseClassName($classname)
    {
        $pos = strrpos($classname, '\\');

        return substr($classname, $pos + 1);
    }



    /**
     * Part du nom complet de la classe (avec son namespace donc)
     * Enlève le début correspondant à $namespace s'il a été trouvé
     *
     * @param string $classname nom de classe, avec son namespace
     *
     * @return string
     */
    public static function truncatedClassName($namespace, $classname)
    {
        if (empty($namespace)) return $classname;
        if (0 === strpos($classname, $namespace)) {
            return substr($classname, strlen($namespace) + strlen('\\'));
        }

        return $classname;
    }



    /**
     * @param $variable
     */
    public static function truncateRightVariable($variable, $term)
    {
        if ($term === substr($variable, -strlen($term))) {
            return substr($variable, 0, -strlen($term));
        }

        return $variable;
    }



    /**
     * Convertit une classe (avec son namespace au besoin) en nom de fichier correspondant
     *
     * @param string $classname
     *
     * @return string
     */
    public static function classNameToFileName($classname)
    {
        return str_replace('\\', '/', $classname) . '.php';
    }



    /**
     * Convertit une classe (avec son namespace au besoin) en CamelCase pour servir de variable ou de nom de méthode
     *
     * @param string $classname
     *
     * @return string
     */
    public static function classNameToCamelCase($classname)
    {
        return str_replace('\\', '', $classname);
    }



    /**
     * Retourne le namespace de la classe
     *
     * @param string $classname nom de classe, avec son namespace
     *
     * @return string
     */
    public static function namespaceClass($classname)
    {
        $pos = strrpos($classname, '\\');

        return substr($classname, 0, $pos);
    }



    /**
     * Retourne le module de la classe à partir de son namespace
     *
     * @param string $classname nom de classe, avec son namespace
     *
     * @return string
     */
    public static function moduleClass($classname)
    {
        $pos = strpos($classname, '\\');

        return substr($classname, 0, $pos);
    }



    /**
     * Retourne la déclaration d'une méthode dans un bloc de commentaire avec "@method"
     *
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    public static function getMethodDocDeclaration(\ReflectionMethod $method, $name = null, $defaultReturn = null)
    {
        $return = $defaultReturn;

        $parameters = $method->getParameters();
        $docComment = explode("\n", $method->getDocComment());
        foreach ($docComment as $docLine) {
            if (false !== strpos($docLine, '@return')) {
                $returnType = trim(substr($docLine, strpos($docLine, '@return') + strlen('@return ')));
                if ('string' == $returnType) $return = 'string';
                if ('integer' == $returnType) $return = 'integer';
                if ('boolean' == $returnType) $return = 'boolean';
                if ('float' == $returnType) $return = 'float';
            }
        }

        $params = '';
        foreach ($parameters as $parameter) {
            if ($params != '') {
                $params .= ', ';
            }

            if ($parameter->isArray()) {
                $params .= 'array ';
            } elseif ($parameter->getClass()) {
                $params .= '\\' . $parameter->getClass()->getName() . ' ';
            }

            $params .= '$' . $parameter->getName();

            if ($parameter->isDefaultValueAvailable()) {
                $defaultValue = $parameter->getDefaultValue();
                if (null === $defaultValue) {
                    $params .= ' = null';
                } elseif ([] === $defaultValue) {
                    $params .= ' = []';
                } else {
                    $params .= ' = ' . var_export($defaultValue, true);
                }
            }
        }

        if (!$name) $name = $method->getName();
        $name = lcfirst($name);

        if ($return) $return .= ' ';

        return "@method $return$name($params)";
    }



    public static function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;
    }



    /**
     * @return ServiceLocatorInterface
     */
    public static function getServiceLocator()
    {
        if (!self::$serviceLocator) {
            throw new \Exception('ServiceLocator introuvable ou module UnicaenCode pas encore chargé');
        }

        return self::$serviceLocator;
    }



    /**
     * @return EntityManager
     */
    public static function getEntityManager($name = 'doctrine.entitymanager.orm_default')
    {
        if (self::getServiceLocator()->has($name)) {
            return self::getServiceLocator()->get($name);
        } else {
            return null;
        }
    }



    /**
     * Retourne l'auteur correspondant au profil actuellement connecté
     *
     * @return string
     */
    public static function getAuthor($default = 'UnicaenCode')
    {
        $authenticationService = self::getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        if ($authenticationService->hasIdentity()) {
            $identity = $authenticationService->getIdentity();
            if (isset($identity['ldap'])) {
                return $identity['ldap']->getDisplayName() . ' <' . str_replace('@', ' at ', $identity['ldap']->getMail()) . '>';
            }
        }

        return $default;
    }



    public static function displayForm( Form $form )
    {
        $vhm = Util::getServiceLocator()->get('viewHelperManager'); /* @var $vhm \Zend\View\HelperPluginManager */
        $view = $vhm->getRenderer();

        ?>
        <style>

            form .form-control {
                margin-left: 10%;
                width: inherit;
            }

            form input[type='text'].form-control {
                margin-left: 10%;
                width: 30em;
            }

            form input[type='submit'].form-control {
                margin-left: 0%;

            }

        </style>
        <?php

        echo $view->form()->openTag($form->prepare());
        foreach( $form->getElements() as $element ){
            echo $view->formControlGroup($element);
        }
        echo $view->form()->closeTag();
    }



    private static function includeSqlFormatter()
    {
        try {
            $config = self::getServiceLocator()->get('UnicaenCode\Config');
            /* @var $config Config */
        } catch (\Exception $e) {
            return false;
        }

        if ($file = $config->getSqlFormatterFile()) {
            if (file_exists($file)) {
                include_once $file;

                return true;
            }
        }

        return false;
    }



    private static function includeGeshi()
    {
        try {
            $config = self::getServiceLocator()->get('UnicaenCode\Config');
            /* @var $config Config */
        } catch (\Exception $e) {
            return false;
        }

        if ($file = $config->getGeshiFile()) {
            if (file_exists($file)) {
                include_once $file;

                return true;
            }
        }

        return false;
    }
}





class EchoSQLLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * display
     *
     * @var boolean
     */
    public $display;

    /**
     *
     * @var integer
     */
    static public $queries = [];



    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        self::$queries[] = ['sql' => $sql, 'params' => $params];
        if ($this->display) {
            Util::highlight($sql, 'sql', true, ['parameters' => $params]);
        }
    }



    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}
