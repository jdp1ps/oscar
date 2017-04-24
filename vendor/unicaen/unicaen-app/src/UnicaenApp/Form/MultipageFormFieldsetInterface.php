<?php
namespace UnicaenApp\Form;

/**
 * Interface 
 *
 * @author bertrand.gauthier@unicaen.fr
 */
interface MultipageFormFieldsetInterface extends \Zend\Form\FieldsetInterface
{
    /**
     * Retourne les labels ainsi que les valeurs des éléments d'un fieldset.
     *
     * @param array $data Données saisies au sein de ce fieldset
     * @return array 'element_name' => array('label' => Label de l'élément, 'value' => Valeur saisie au format texte)
     */
    public function getLabelsAndValues($data = null);
    
}
