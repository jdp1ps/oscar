<?php
namespace UnicaenAppTest\Form\TestAsset;

use InvalidArgumentException;
use UnicaenApp\Form\Element\MultipageFormNav;
use UnicaenApp\Form\MultipageForm;
use Zend\Form\Element\Submit;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ContactMultipageForm extends MultipageForm
{
    const FIELDSET_1_NAME = 'identite';
    const FIELDSET_2_NAME = 'adresse';
    const FIELDSET_3_NAME = 'message';
    
    /**
     * 
     */
    public function prepareElements()
    {
        $this->add(new IdentiteFieldset(self::FIELDSET_1_NAME))
             ->add(new AdresseFieldset(self::FIELDSET_2_NAME))
             ->add(new MessageFieldset(self::FIELDSET_3_NAME))
             ->add(new Submit('save', array('label'=>"Enregistrer")));
    }

    /**
     * 
     * @param string $fieldsetName 
     * @param bool $valid
     * @param string $navElementName
     * @return array
     * @throws InvalidArgumentException
     */
    public function createSamplePostDataForFieldset($fieldsetName, $valid = true, $navElementName = MultipageFormNav::NEXT)
    {
        switch ($fieldsetName) {
            case self::FIELDSET_1_NAME:
                $data = array(
                    'nom'    => $valid ? "Hochon" : "",
                    'prenom' => "Paul",
                    'civ'    => "M",
                );
                break;
            case self::FIELDSET_2_NAME:
                $data = array(
                    'email' => $valid ? "paul.hochon@domain.fr" : "paul.hochon@domain",
                );
                break;
            case self::FIELDSET_3_NAME:
                $data = array(
                    'message' => "Hello, world!",
                );
                break;
            default:
                $data = array();
                break;
        }
        if ($navElementName) {
            $data = array_merge($data, array(
                self::NAME_NAV => array($navElementName => $navElementName),
            ));
        }
        return array(
            $fieldsetName => $data,
        );
    }
}