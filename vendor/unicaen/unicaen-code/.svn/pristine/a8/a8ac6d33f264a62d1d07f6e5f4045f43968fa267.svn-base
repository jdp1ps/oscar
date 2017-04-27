<?php

use UnicaenCode\Form\ElementMaker;
use UnicaenCode\Util;

/**
 * @var $this       \Zend\View\Renderer\PhpRenderer
 * @var $controller \Zend\Mvc\Controller\AbstractController
 * @var $viewName   string
 */

?>
    <h1>Création d'une nouvelle assertion</h1>
    <h3>Etape 1 : Paramétrage</h3>

<?php

$form = new \Zend\Form\Form();
$form->add(ElementMaker::selectModule(
    'module', 'Module dans lequel sera placé votre assertion'
));
$form->add(ElementMaker::text(
    'classname', 'Nom de classe de l\'assertion (en CamelCase, avec éventuellement un namespace avant : MonNamespace\Exemple)', 'Exemple'
));
$form->add(ElementMaker::submit('generate', 'Générer l\'assertion'));
$form->setData($controller->getRequest()->getPost());

Util::displayForm($form);

if ($controller->getRequest()->isPost() && $form->isValid()) {

    $sCodeGenerator = $controller->getServiceLocator()->get('UnicaenCode\CodeGenerator');
    /* @var $sCodeGenerator \UnicaenCode\Service\CodeGenerator */

    $params = $sCodeGenerator->generateServiceParams([
        'type'              => 'Assertion',
        'classname'         => $form->get('module')->getValue() . '\\Assertion\\' . $form->get('classname')->getValue() . 'Assertion',
        'name'              => 'assertion' . str_replace('\\', '', $form->get('classname')->getValue()),
        'useServiceLocator' => false,
        'generateTrait'     => false,
        'generateInterface' => false,
    ]);

    $config = $params['Config'];
    $class = $params['Class'];

    ?>

    <h3>Etape 2 : Création du fichier source de l'assertion</h3>
    <?php
    $sCodeGenerator->generateFiles($params);
    ?>
    <div class="alert alert-info">Le fichier est récupérable dans le
        dossier <?php echo $sCodeGenerator->getOutputDir() ?></div>

    <h3>Etape 3 : Déclaration dans le fichier de configuration</h3>
    <?php $sCodeGenerator->generateFile($params['Config'], false); ?>
    <div class="alert alert-warning">
        Vous devez vous-même placer ces informations dans le fichier de configuration de votre
        module.
    </div>

    <?php
}