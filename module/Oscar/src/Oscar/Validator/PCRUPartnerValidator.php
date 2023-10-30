<?php


namespace Oscar\Validator;


use Oscar\Entity\Organization;
use Laminas\Validator\AbstractValidator;

/**
 * Cette classe permet de vérifier si une structure est éliglible à PCRU comme PARTENAIRE
 *
 * @package Oscar\Validator
 */
class PCRUPartnerValidator extends AbstractValidator
{
    const PCRU_VALIDATOR_CODE_PARTNER = 'PCRU_VALIDATOR_CODE_PARTNER';


    protected $messageTemplates = [
        self::PCRU_VALIDATOR_CODE_PARTNER => "Le partenaire PCRU '%s' a le rôle mais n'a ni SIRET, TVA Intra ou DUNS",
    ];

    /**
     * @param Organization $value
     * @return bool|void
     */
    public function isValid($value) :bool
    {
        if( !($value->getSiret() || $value->getDuns() || $value->getTvaintra()) ){
            $this->error(self::PCRU_VALIDATOR_CODE_PARTNER, $value);
            return false;
        }

        return true;
    }

}