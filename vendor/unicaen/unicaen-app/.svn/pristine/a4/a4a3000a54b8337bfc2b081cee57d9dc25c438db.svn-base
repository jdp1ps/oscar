<?php

namespace UnicaenApp\Filter;

use Zend\Filter\AbstractFilter;
use Zend\View\Model\ViewModel;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\View\Model\ModalViewModel;

/**
 * Filtre permettant d'imbriquer un modèle de vue dans un modèle
 * de vue correspondant à la DIV interne d'une fenêtre modale Bootstrap 3 (div.modal-dialog).
 * 
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ModalViewModelFilter extends AbstractFilter
{
    /*******************************************************
        <div class="modal fade">
          <div class="modal-dialog">   )
            [...]                      > marquage généré
          </div>                       )
        </div>
    ********************************************************/
    
    /**
     * Retourne un model de vue correspondant au contenu d'une fenêtre modale Bootstrap 3.
     *
     * @param  ViewModel $value
     * @return ViewModel
     * @throws LogicException Titre null
     */
    public function filter($viewModel)
    {
        $f = new ModalInnerViewModelFilter();
        $modalInnerViewModel = $f->filter($viewModel);

        $modalInnerViewModel->setTerminal(false);
        
        $modalViewModel = new ModalViewModel();
        $modalViewModel
                ->setVariables(array('dialogDivId' => null))
                ->addChild($modalInnerViewModel, 'dialogContent');
            
        return $modalViewModel;
    }
}