<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/12/15 11:36
 * @copyright Certic (c) 2015
 */

namespace Oscar\Form;


use Laminas\Form\Element\File;
use Laminas\Form\Form;
use Laminas\ModuleManager\Feature\InputFilterProviderInterface;
use Oscar\Hydrator\SignedDocumentFormHydrator;

class SignedDocumentForm extends Form implements InputFilterProviderInterface
{
    /**
     * ContractDocumentForm constructor.
     */
    public function __construct(string $name = 'signeddocument')
    {
        parent::__construct($name);
        $this->setHydrator(new SignedDocumentFormHydrator());
        $this->add([
           'name'   => 'file',
           'options' => [
               'label' => "Fichier à signer"
           ],
           'attributes'    => [
               'class'       => 'form-control',
               'placeholder'   => 'Fichier à signer',
           ],
           'type'=>File::class
       ]);
        $this->add([
                       'name'   => 'persons',
                       'type'=>'hidden'
                   ]);
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