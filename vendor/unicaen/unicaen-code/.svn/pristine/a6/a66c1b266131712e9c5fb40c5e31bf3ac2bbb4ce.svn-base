<?php

namespace UnicaenCode\Form;

use UnicaenCode\Util;
use Zend\Form\Element;

class ElementMaker
{

    public static function text($name, $label, $value = null)
    {
        $options = [
            'type'    => 'Text',
            'name'    => $name,
            'options' => [
                'label' => $label,
            ],
        ];

        if ($value !== null) {
            $options['attributes']['value'] = $value;
        }

        return $options;
    }



    public static function checkbox( $name, $label, $value = false )
    {
        $options = [
            'type'    => 'Checkbox',
            'name'    => $name,
            'options' => [
                'label' => $label,
            ],
        ];

        if ($value !== null) {
            $options['attributes']['value'] = $value;
        }

        return $options;
    }



    public static function submit($name, $value)
    {
        return [
            'type'       => 'Submit',
            'name'       => $name,
            'attributes' => [
                'value' => $value,
                'class' => 'btn btn-primary',
            ],
        ];
    }



    public static function select($name, $label, $options, $value)
    {
        $select = new Element\Select($name);
        $select->setLabel($label);
        $select->setValueOptions($options);
        $select->setValue($value);

        return $select;
    }



    public static function selectModule($name, $label, $value = 'Application')
    {
        $si = Util::getServiceLocator()->get('UnicaenCode\Introspection');
        /* @var $si \UnicaenCode\Service\Introspection */

        $options = [
            'module' => [
                'label'   => 'Module',
                'options' => [],
            ],
            'vendor' => [
                'label'   => 'Vendor',
                'options' => [],
            ],
        ];
        $modules = $si->getModules(true);
        ksort($modules);
        foreach ($modules as $mName => $module) {
            if ($module['in-vendor']) {
                $options['vendor']['options'][$mName] = $mName;
            } else {
                $options['module']['options'][$mName] = $mName;
            }
        }

        return self::select($name, $label, $options, $value);
    }



    public static function selectEntity($name, $label, $namespace, $value = null)
    {
        $si = Util::getServiceLocator()->get('UnicaenCode\Introspection');
        /* @var $si \UnicaenCode\Service\Introspection */

        $options  = [];
        $entities = $si->getDbEntities($namespace);
        ksort($entities);
        foreach ($entities as $entity) {
            if (0 === strpos($entity, $namespace)) {
                $entity = substr($entity, strlen($namespace) + 1);
            }
            $options[$entity] = $entity;
        }

        return self::select($name, $label, $options, $value);
    }
}