<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/12/15 11:36
 * @copyright Certic (c) 2015
 */

namespace Oscar\Form;


use Zend\Form\Form;
use Zend\ModuleManager\Feature\InputFilterProviderInterface;

class AdministrativeDocumentForm extends Form implements InputFilterProviderInterface
{
    /**
     * ContractDocumentForm constructor.
     */
    public function __construct()
    {
        parent::__construct('contractdocument');
    }


    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getInputFilterConfig()
    {
        // TODO: Implement getInputFilterConfig() method.
    }
}