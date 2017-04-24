<?php

use UnicaenCode\Form\ElementMaker;
use UnicaenCode\Util;

/**
 * @var $this       \Zend\View\Renderer\PhpRenderer
 * @var $controller \Zend\Mvc\Controller\AbstractController
 * @var $viewName   string
 */

?>
    <h1>Création d'un nouveau contrôleur</h1>
    <h3>Etape 1 : Paramétrage</h3>

<?php

$form = new \Zend\Form\Form();
$form->add(ElementMaker::selectModule(
    'module', 'Module dans lequel sera placé votre contrôleur'
));
$form->add(ElementMaker::text(
    'name', 'Nom de classe du contrôleur (en CamelCase)', 'ExempleDeControlleur'
));
$form->add(ElementMaker::text(
    'route', 'Route par laquelle y accéder depuis une URL (en minus-cules ou snake_case)', 'exemple-de-controlleur'
));
$form->add(ElementMaker::submit('generate', 'Générer le contrôleur'));
$form->setData($controller->getRequest()->getPost());

Util::displayForm($form);

if ($controller->getRequest()->isPost() && $form->isValid()) {

    $name            = $form->get('name')->getValue();
    $route           = $form->get('route')->getValue();
    $module          = $form->get('module')->getValue();
    $targetFullClass = $module . '\\Controller\\' . $name . 'Controller';

    $sCodeGenerator = $controller->getServiceLocator()->get('UnicaenCode\CodeGenerator');
    /* @var $sCodeGenerator \UnicaenCode\Service\CodeGenerator */

    $params = $sCodeGenerator->generateControllerParams($targetFullClass, $name, $route, $module);

    $configFileName = 'module.config.php';

    ?>

    <h3>Etape 2 : Création du fichier source du contrôleur</h3>
    <?php $sCodeGenerator->setTemplate('Controller')->setParams($params)->generateToHtml($params['fileName'])->generateToFile($params['fileName']); ?>
    <div class="alert alert-info">Le fichier est récupérable dans le
        dossier <?php echo $sCodeGenerator->getOutputDir() ?></div>

    <h3>Etape 3 : Déclaration dans le fichier de configuration</h3>
    <?php $sCodeGenerator->setTemplate('ControllerConfig')->setParams($params)->generateToHtml($configFileName); ?>
    <div class="alert alert-warning">
        Vous devez vous-même placer ces informations dans le fichier de configuration de votre
        module.<br/>
        Attention : ces informations partent du principe que vous utilisez le système de gestion des privilèges d'UnicaenAuth.
        Si ce n'est pas le cas alors il vous faudra adapter les autorisations à votre fonctionnement.
    </div>

    <?php
}