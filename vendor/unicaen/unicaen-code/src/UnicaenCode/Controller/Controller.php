<?php
namespace UnicaenCode\Controller;

use UnicaenCode\Service\Traits\ConfigAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class Controller extends AbstractActionController
{
    use ConfigAwareTrait;

    public function indexAction()
    {
        $viewName  = $this->params()->fromRoute('view');

        $viewDirs = $this->getServiceConfig()->getViewDirs();
        $viewFile = null;
        foreach( $viewDirs as $viewDir ){
            if (file_exists($viewDir.'/'.$viewName.'.php')){
                $viewFile = $viewDir.'/'.$viewName.'.php';
                break;
            }
        }

        $viewModel = new ViewModel;
        $viewModel->setTemplate('unicaen-code/index');
        $viewModel->setVariables([
            'controller' => $this,
            'viewName'   => $viewName,
            'viewFile'   => $viewFile,
        ]);

        return $viewModel;
    }
}