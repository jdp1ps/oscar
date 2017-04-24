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


$sCodeGenerator->setTemplate('FormAwareTrait');

echo '<h1>Liste des fichiers générés</h1  code>';

foreach( $sConfig->getNamespacesForms() as $namespace ) {
    echo '<h2>' . $namespace . '</h2>';

    $forms = $sIntrospection->getInvokableForms($namespace);
    foreach ($forms as $name => $fullClass) {
        $params = $sCodeGenerator->generateFormParams([
            'type'  => false === strpos($fullClass, 'Fieldset') ? 'Form' : 'Fieldset',
            'classname'         => $fullClass,
            'name'              => $name,
            'generateInterface' => false,
            'generateTrait'     => true,
            'generateConfig'    => false,
            'rootNamespace'     => $namespace,
        ]);
        unset($params['Class']);
        $sCodeGenerator->generateFiles($params);
    }
}

echo '<div class="alert alert-info" role="alert">Les fichiers sont récupérables dans le dossier '.$sCodeGenerator->getOutputDir().'</div>';