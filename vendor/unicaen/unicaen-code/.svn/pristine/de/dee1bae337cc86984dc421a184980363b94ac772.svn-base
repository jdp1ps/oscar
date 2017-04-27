<?php

use UnicaenCode\Form\ElementMaker;
use UnicaenCode\Util;

/**
 * @var $this       \Zend\View\Renderer\PhpRenderer
 * @var $controller \Zend\Mvc\Controller\AbstractController
 * @var $viewName   string
 */

?>
    <h1>Création d'une nouvelle aide de vue</h1>
    <h3>Etape 1 : Paramétrage</h3>

<?php

$form = new \Zend\Form\Form();
$form->add(ElementMaker::selectModule(
    'module', 'Module dans lequel sera placé votre aide de vue'
));
$form->add(ElementMaker::text(
    'classname', 'Nom de classe de l\'aide de vue (en CamelCase, avec éventuellement un namespace avant : MonNamespace\CamelCase)', 'ExempleAideVue'
));
$form->add(ElementMaker::text(
    'name', 'Nom pour le ServiceLocator (en lowerCamelCase)', 'exempleAideVue'
));
$form->add(ElementMaker::checkbox(
    'useServiceLocator', 'Accéder au ServiceLocator', true
));
$form->add(ElementMaker::submit('generate', 'Générer l\'aide de vue'));
$form->setData($controller->getRequest()->getPost());

Util::displayForm($form);

if ($controller->getRequest()->isPost() && $form->isValid()) {

    $sCodeGenerator = $controller->getServiceLocator()->get('UnicaenCode\CodeGenerator');
    /* @var $sCodeGenerator \UnicaenCode\Service\CodeGenerator */

    //$params = $sCodeGenerator->generateViewHelperParams($targetFullClass, $name, $module, $useServiceLocator);
    $params = $sCodeGenerator->generateViewHelperParams([
        'classname'         => $form->get('module')->getValue() . '\\View\\Helper\\' . $form->get('classname')->getValue() . 'ViewHelper',
        'name'              => $form->get('name')->getValue(),
        'useServiceLocator' => $form->get('useServiceLocator')->getValue(),
    ]);

    $configFileName = 'module.config.php';

    ?>

    <h3>Etape 2 : Création du fichier source de l'aide de vue</h3>
    <?php $sCodeGenerator->generateFiles($params); ?>
    <div class="alert alert-info">Le fichier est récupérable dans le
        dossier <?php echo $sCodeGenerator->getOutputDir() ?></div>

    <h3>Etape 3 : Déclaration dans le fichier de configuration</h3>
    <?php $sCodeGenerator->generateFile($params['Config']); ?>
    <div class="alert alert-warning">
        Vous devez vous-même placer ces informations dans le fichier de configuration de votre
        module.
    </div>

    <h3>Etape 4 : Mise à jour de votre phpRenderer</h3>

    <a href="<?php echo $this->url('unicaen-code', ['view' => 'GenerateApplicationPhpRenderer']); ?>">Aller au générateur de
        phpRenderer</a>
    <?php
}