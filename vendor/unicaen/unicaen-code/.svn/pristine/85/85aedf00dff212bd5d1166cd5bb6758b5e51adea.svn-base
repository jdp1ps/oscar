<?php

use UnicaenCode\Util;

/**
 * @var $this       \Zend\View\Renderer\PhpRenderer
 * @var $controller \Zend\Mvc\Controller\AbstractController
 * @var $viewName   string
 */

$sConfig = $controller->getServiceLocator()->get('UnicaenCode\Config');
/* @var $sConfig \UnicaenCode\Service\Config */

$sIntrospection = $controller->getServiceLocator()->get('UnicaenCode\Introspection');
/* @var $sIntrospection \UnicaenCode\Service\Introspection */

$sCodeGenerator = $controller->getServiceLocator()->get('UnicaenCode\CodeGenerator');
/* @var $sCodeGenerator \UnicaenCode\Service\CodeGenerator */


$sCodeGenerator->setTemplate('EntityAwareTrait');

echo '<h1>Liste des fichiers générés</h1>';

foreach ($sConfig->getNamespacesEntities() as $namespace) {
    echo '<h2>' . $namespace . '</h2>';

    $entities = $sIntrospection->getDbEntities($namespace);
    foreach ($entities as $fullClass) {
        $params = $sCodeGenerator->generateEntityParams([
            'generateTrait' => true,
            'classname'     => $fullClass,
            'rootNamespace' => $namespace,
        ]);
        $sCodeGenerator->generateFiles($params);
    }
}

echo '<div class="alert alert-info" role="alert">Les fichiers sont récupérables dans le dossier ' . $sCodeGenerator->getOutputDir() . '</div>';