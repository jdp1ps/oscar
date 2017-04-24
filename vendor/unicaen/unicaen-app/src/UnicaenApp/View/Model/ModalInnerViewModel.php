<?php

namespace UnicaenApp\View\Model;

use Zend\View\Model\ViewModel;

/**
 * Description of ModalViewModel
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModalInnerViewModel extends ViewModel
{
    public function __construct($variables = null, $options = null)
    {
        parent::__construct($variables, $options);
        
        $this->setTemplate('unicaen-app/modal-inner-wrapper.phtml');
    }
}