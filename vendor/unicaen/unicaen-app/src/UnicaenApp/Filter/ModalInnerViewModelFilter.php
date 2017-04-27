<?php

namespace UnicaenApp\Filter;

use Zend\Filter\AbstractFilter;
use Zend\View\Model\ViewModel;
use UnicaenApp\View\Model\ModalInnerViewModel;
use UnicaenApp\Exception\LogicException;

/**
 * Filtre permettant d'imbriquer un modèle de vue dans un modèle
 * de vue correspondant à la DIV interne d'une fenêtre modale Bootstrap 3 (div.modal-dialog).
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModalInnerViewModelFilter extends AbstractFilter
{
    /*******************************************************
        <div class="modal fade">
            <div class="modal-dialog"> 
                <div class="modal-header">  \
                    [...]                    |
                </div>                       |
                <div class="modal-body">     |
                    [...]                     > marquage généré
                </div>                       |
                <div class="modal-footer">   |
                    [...]                    |
                </div>                      /
            </div>
        </div>
    ********************************************************/
    
    /**
     * Retourne un model de vue correspondant au contenu d'une fenêtre modale Bootstrap 3.
     *
     * @param  ViewModel $viewModel
     * @return ModalInnerViewModel
     * @throws LogicException Titre null
     */
    public function filter($viewModel)
    {
        $title = $viewModel->getVariable('title');
        
        $viewModel->setTerminal(false);

        $modalViewModel = new ModalInnerViewModel();
        $modalViewModel
                ->addChild($viewModel, 'bodyContent')
                ->setVariables(array('title' => $title));
        
        return $modalViewModel;
    }
}