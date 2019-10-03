<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 02/10/19
 * Time: 18:24
 */

namespace Oscar\Controller;


use Zend\Mvc\Console\Controller\AbstractConsoleController;

class TestConsoleController extends AbstractConsoleController
{
    public function testAction(){
        die("OK");
    }

}