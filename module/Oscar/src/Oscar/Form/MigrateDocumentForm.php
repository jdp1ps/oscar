<?php
/**
 * @author Hervé Marie<herve.marie@unicaen.fr>
 * @date: 17/10/22 14:52
 * @copyright Certic (c) 2022
 */

namespace Oscar\Form;

use Doctrine\ORM\EntityManager;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\ContractDocumentRepository;
use Oscar\Entity\TabDocument;
use Oscar\Hydrator\TabDocumentFormHydrator;
use Zend\Form\Element\Select;
use Zend\Form\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class MigrateDocumentForm extends Form implements InputFilterProviderInterface
{

    /**
     * @param array $roles
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct('migratedocument');

        $this->setHydrator(new TabDocumentFormHydrator($em));

        /** @var ContractDocumentRepository $tabDocumentRepository */
        $tabDocumentRepository = $em->getRepository(ContractDocument::class);

        $label = 'Type de document';
        $this->add([
            'name'   => 'documentType',
            'type' => Select::class,
            'options' => [
                'label' => $label,
                'value_options' => $tabDocumentRepository->getTypesSelectable(true)
            ],
            'attributes'    => [
                'class'       => 'form-control input-lg',
                'placeholder'   => $label,
            ],
        ]);

        $this->add(
            [
                'type' => Select::class,
                'name' => 'tabDocument',
                'options' => [
                    'value_options' => $tabDocumentRepository->getTabDocumentSelectable()
                ]
            ]);

        $this->add(array(
            'name'  => 'secure',
            'type'  => 'Csrf',
        ));
    }

    /**
     * Filter obligatoire (champs obligatoires)
     *
     * @return array
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'documentType' => [ 'required' => true ],
            'tabDocument' => [ 'required' => false ]
        ];
    }
}
