<?php

/**
 * @var $this       \Zend\View\Renderer\PhpRenderer
 * @var $controller \Zend\Mvc\Controller\AbstractController
 * @var $viewName   string
 */

$sIntrospection = $controller->getServiceLocator()->get('UnicaenCode\Introspection');
/* @var $sIntrospection \UnicaenCode\Service\Introspection */

$sCodeGenerator = $controller->getServiceLocator()->get('UnicaenCode\CodeGenerator');
/* @var $sCodeGenerator \UnicaenCode\Service\CodeGenerator */

$sPrivileges = $controller->getServiceLocator()->get('UnicaenAuth\Service\Privilege');
/* @var $sPrivileges \UnicaenAuth\Service\PrivilegeService */

$privileges = $sPrivileges->getList();

$sCodeGenerator->setTemplate('Privileges');

$privilegesConsts = [];
$constMaxLen = 0;
foreach ($privileges as $privilege) {
    $value = var_export($privilege->getFullCode(), true);
    $const = strtoupper($privilege->getFullCode());
    $const = str_replace( ['-',' '], ['_','_'], $const );
    if (strlen($const) > $constMaxLen) $constMaxLen = strlen($const);

    if (! defined(\UnicaenAuth\Provider\Privilege\Privileges::class.'::'.$const)){
        $privilegesConsts[$const] = $value;
    }
}

$data = '';
foreach( $privilegesConsts as $const => $value ){
    if ('' !== $data) $data .= "\n";
    $data .= "    const ".str_pad($const,$constMaxLen)." = $value;";
}

echo '<h1>Génération de la classe listant les constantes de privilèges</h1>';

$params   = ['privileges' => $data];
$fileName = 'Application/Provider/Privilege/Privileges.php';
$sCodeGenerator->setParams($params)->generateToHtml($fileName)->generateToFile($fileName);


echo '<div class="alert alert-info" role="alert">Le fichier est récupérable dans le dossier ' . $sCodeGenerator->getOutputDir() . '</div>';