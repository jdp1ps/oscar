<?php
namespace UnicaenAppTest\Form\TestAsset;

use UnicaenApp\Form\MultipageFormFieldsetInterface;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\Element\Text;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class IdentiteFieldset extends Fieldset implements InputFilterProviderInterface, MultipageFormFieldsetInterface
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
 
        $this->setLabel("Vous")
             ->add(new Text('nom', array('label' => "Nom")))
             ->add(new Text('prenom', array('label' => "Prénom")))
             ->add(new MultiCheckbox('civ', array('label'=>"Civilité", 'value_options' => array('Melle'=>'Melle', 'Mme'=>'Mme', 'M'=>'M'))));
    }
 
    public function getInputFilterSpecification()
    {
        return array(
            'nom' => array(
                'required' => true,
                'validators' => array(
                    array(
                        'name'=> 'NotEmpty',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array('isEmpty' => "Vous devez renseigner votre nom"),
                        )
                    ),
                ),
            ),
        );
    }
    
    /**
     * Retourne les labels ainsi que les valeurs des éléments d'un fieldset.
     *
     * @param array $data Données saisies au sein de ce fieldset
     * @return array 'element_name' => array('label' => Label de l'élément, 'value' => Valeur saisie au format texte)
     */
    public function getLabelsAndValues($data = null)
    {
        if ($data && array_key_exists($this->getName(), $data)) {
            $data = $data[$this->getName()];
        }
        $result = array();
        $result['nom'] = array(
            'label' => "Nom",
            'value' => isset($data['nom']) ? $data['nom'] : "Non spécifié"
        );
        $result['prenom'] = array(
            'label' => "Prénom",
            'value' => isset($data['prenom']) ? $data['prenom'] : "Non spécifié"
        );
        $result['civ'] = array(
            'label' => "Civilité",
            'value' => isset($data['civ']) ? $data['civ'] : "???"
        );
        return $result;
    }
}
