<?php


namespace Oscar\Validator;


use Oscar\Entity\Organization;
use Zend\Validator\AbstractValidator;

/**
 * Cette classe permet de vérifier si une structure est éliglible à PCRU comme UNIT
 *
 * @package Oscar\Validator
 */
class PCRUUnitValidator extends AbstractValidator
{
    const PCRU_VALIDATOR_UNIT = 'PCRU_VALIDATOR_UNIT';
    const PCRU_VALIDATOR_LABINTEL = 'PCRU_VALIDATOR_UNIT_LABINTEL';


    protected $messageTemplates = [
        self::PCRU_VALIDATOR_UNIT => "La structure n'est pas une unité valide pour PCRU",
        self::PCRU_VALIDATOR_LABINTEL => "La structure '%s' n'a pas de code LABINTEL",
    ];

    /**
     * @param Organization $value
     * @return bool|void
     */
    public function isValid($value)
    {
        if( !$value->getLabintel() ){
            $this->error(sprintf(self::PCRU_VALIDATOR_LABINTEL, $value));
            return false;
        }

        return true;
    }

}