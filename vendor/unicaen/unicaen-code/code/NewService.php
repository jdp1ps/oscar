<?php

use UnicaenCode\Form\ElementMaker;
use UnicaenCode\Util;

/**
 * @var $this       \Zend\View\Renderer\PhpRenderer
 * @var $controller \Zend\Mvc\Controller\AbstractController
 * @var $viewName   string
 */

?>
    <h1>Création d'un nouveau service</h1>
    <h3>Etape 1 : Paramétrage</h3>

<?php

$form = new \Zend\Form\Form();
$form->add(ElementMaker::selectModule(
    'module', 'Module dans lequel sera placé votre service'
));
$form->add(ElementMaker::text(
    'namespace', 'Espace de noms (sans mentionner le module)', 'Service'
));
$form->add(ElementMaker::text(
    'classname', 'Nom de classe du service (en CamelCase) : ExempleService)', 'ExempleService'
));
$form->add(ElementMaker::text(
    'name', 'Nom pour le ServiceLocator (en lowerCamelCase)', 'exemple'
));
$form->add(ElementMaker::checkbox(
    'useServiceLocator', 'Accéder au ServiceLocator', true
));
$form->add(ElementMaker::checkbox(
    'generateTrait', 'Générer un trait', true
));
$form->add(ElementMaker::checkbox(
    'generateInterface', 'Générer une interface', false
));
$form->add(ElementMaker::submit('generate', 'Générer le service'));
$form->setData($controller->getRequest()->getPost());

Util::displayForm($form);

if ($controller->getRequest()->isPost() && $form->isValid()) {

    $sCodeGenerator = $controller->getServiceLocator()->get('UnicaenCode\CodeGenerator');
    /* @var $sCodeGenerator \UnicaenCode\Service\CodeGenerator */

    $params = $sCodeGenerator->generateServiceParams([
        'classname'         => $form->get('module')->getValue() . '\\' . $form->get('namespace')->getValue() . '\\' . $form->get('classname')->getValue(),
        'name'              => $form->get('name')->getValue(),
        'useServiceLocator' => $form->get('useServiceLocator')->getValue(),
        'generateTrait'     => $form->get('generateTrait')->getValue(),
        'generateInterface' => $form->get('generateInterface')->getValue(),
    ]);

    ?>

    <h3>Etape 2 : Création des fichiers sources du service</h3>
    <?php
    $sCodeGenerator->generateFiles($params);
    ?>
    <div class="alert alert-info">Les fichiers sont récupérables dans le
        dossier <?php echo $sCodeGenerator->getOutputDir() ?></div>

    <h3>Etape 3 : Déclaration dans le fichier de configuration</h3>
    <?php $sCodeGenerator->generateFile($params['Config'], false); ?>
    <div class="alert alert-warning">
        Vous devez vous-même placer ces informations dans le fichier de configuration de votre
        module.
    </div>

    <?php
}