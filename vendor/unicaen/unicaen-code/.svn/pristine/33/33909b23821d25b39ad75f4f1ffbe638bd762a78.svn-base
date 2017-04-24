<?php

use UnicaenCode\Util;

/**
 * @var $this       \Zend\View\Renderer\PhpRenderer
 * @var $controller \Zend\Mvc\Controller\AbstractController
 * @var $viewName   string
 */

$sIntrospection = $controller->getServiceLocator()->get('UnicaenCode\Introspection');
/* @var $sIntrospection \UnicaenCode\Service\Introspection */

$sCodeGenerator = $controller->getServiceLocator()->get('UnicaenCode\CodeGenerator');
/* @var $sCodeGenerator \UnicaenCode\Service\CodeGenerator */


$sCodeGenerator->setTemplate('ApplicationPhpRenderer');

$fullClassName = 'Application\View\Renderer\PhpRenderer';

?>
<h1>Génération du PhpRenderer de l'application</h1>

<div class="alert alert-warning">
    <span class="glyphicon glyphicon glyphicon-thumbs-up" style="float: left;font-size:50pt;margin-right:.2em;margin-bottom:.2em"></span>
    Le PhpRenderer permet de bénéficier de l'auto-complétion des view helpers depuis vos vues.
    Pour l'utiliser, il convient de déclarer la ligne suivante au début de chaque vue :

    <?php phpDump('/* @var $this \\'.$fullClassName.' */');?>

</div>

<?php

$vhs = $sIntrospection->getViewHelpers();

$methods = [];
foreach( $vhs as $key => $class ){
    $rc = new \ReflectionClass($class);
    if ($rc->hasMethod('__invoke')){
        $method = $rc->getMethod('__invoke');
        $methods[] = ' * '.\UnicaenCode\Util::getMethodDocDeclaration( $rc->getMethod('__invoke'), $key, '\\'.$class );
    }else{
        $methods[] = " * @method \\$class $key()";
    }
}

$params = [
    'methods' => implode("\n",$methods)
];
$fileName = Util::classNameToFileName($fullClassName);
$sCodeGenerator->setParams($params)->generateToHtml($fileName)->generateToFile($fileName);

echo '<div class="alert alert-info" role="alert">Le fichier est récupérable dans le dossier '.$sCodeGenerator->getOutputDir().'</div>';