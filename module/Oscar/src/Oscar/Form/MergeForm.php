<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 13/10/15 15:44
 * @copyright Certic (c) 2015
 */

namespace Oscar\Form;

use Laminas\Form\Element\Select;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Hydrator\HydratorInterface;

/**
 * Formulaire générique pour la fusion des données statiques entre une ou
 * plusieurs entités.
 *
 * Class MergeForm
 * @package Oscar\Form
 */
class MergeForm extends Form implements InputFilterProviderInterface
{
    private $objects;
    private $ignored;
    private $required = [];
    private $selectableData;

    /**
     * Préparation du formulaires à partir des données.
     *
     * @param HydratorInterface $hydrator
     * @param $objects
     * @param array $ignored
     */
    public function preInit(HydratorInterface $hydrator, $objects, $ignored = ['id'])
    {
        $this->hydrator = $hydrator;
        $this->objects = $objects;
        $this->ignored = $ignored;
    }

    public function getSelectableData()
    {
        if ($this->selectableData === null) {
            $this->selectableData = [];
            foreach ($this->objects as $object) {
                foreach ($this->hydrator->extract($object) as $fieldName=>$data) {
                    if (in_array($fieldName, $this->ignored)) {
                        continue;
                    }
                    if (!isset($this->selectableData[$fieldName])) {
                        $this->selectableData[$fieldName] = [];
                    }
                    if (!isset($this->selectableData[$fieldName][$data])) {
                        $this->selectableData[$fieldName][$data] = $data;
                        $this->required[$fieldName]=[
                            'required' => false
                        ];
                    }
                }
            }
        }
        return $this->selectableData;
    }

    public function init()
    {
        $this->setHydrator($this->hydrator);
        $datas = $this->getSelectableData();

        foreach ($datas as $fieldName=>$values) {
            $select = new Select($fieldName);
            $select->setLabel($fieldName);
            $select->setValueOptions($values);
            $this->add($select);
        }
    }

    public function getInputFilterSpecification()
    {
        return $this->required;
    }
}
