<?php

namespace Oscar\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * @author  Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 */
class IndexController extends AbstractActionController
{
    private function userLogged()
    {
        return ''.$this->plugin('userInfo') !== '';
    }
    public function indexAction()
    {
        die($this->userLogged());

        return new ViewModel();
    }
}
