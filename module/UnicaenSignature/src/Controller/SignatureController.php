<?php

namespace UnicaenSignature\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class SignatureController extends AbstractActionController {

    public function indexAction(): ViewModel
    {
        return new ViewModel([]);
    }
}