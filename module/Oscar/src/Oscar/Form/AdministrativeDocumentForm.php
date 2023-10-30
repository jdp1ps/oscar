<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/12/15 11:36
 * @copyright Certic (c) 2015
 */

namespace Oscar\Form;


use Laminas\Form\Form;
use Laminas\ModuleManager\Feature\InputFilterProviderInterface;

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
     * Expected to return \Laminas\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Laminas\ServiceManager\Config
     */
    public function getInputFilterConfig()
    {
        // TODO: Implement getInputFilterConfig() method.
    }
}