<?php

use UnicaenCode\Form\ElementMaker;
use UnicaenCode\Util;

/**
 * @var $this       \Zend\View\Renderer\PhpRenderer
 * @var $controller \Zend\Mvc\Controller\AbstractController
 * @var $viewName   string
 */

$sCodeGenerator = $controller->getServiceLocator()->get('UnicaenCode\CodeGenerator');
/* @var $sCodeGenerator \UnicaenCode\Service\CodeGenerator */

?>
<h1>Création d'une nouvelle vue</h1>
<div class="alert alert-warning">
    <p>Le code généré ici n'a d'utilité que si vous utilisez le générateur de phpRenderer, pour pouvoir bénéficier des outils de refactoring et de l'auto-complétion des aides de vues dans les vues.

    <a href="<?php echo $this->url('unicaen-code', ['view' => 'GenerateApplicationPhpRenderer']); ?>">Aller au générateur de phpRenderer</a></p>

    <p>Vous devez vous-même créer votre fichier de vue et copier/coller ce contenu.</p>
</div>

<?php
$sCodeGenerator->setTemplate('View')->setParams(['void' => 'void'])->generateToHtml('votre-nouvelle-vue.phtml');
