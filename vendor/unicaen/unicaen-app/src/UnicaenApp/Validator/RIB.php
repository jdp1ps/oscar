<?php

namespace UnicaenApp\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Description of NumeroINSEE
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class RIB extends AbstractValidator
{
    const NOT_VALID         = 'notValid';
    const MALFORMED_BANQUE  = 'malformedBanque';
    const MALFORMED_GUICHET = 'malformedGuichet';
    const MALFORMED_COMPTE  = 'malformedCompte';
    const MALFORMED_CLE     = 'malformedCle';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_VALID         => "Le RIB n'est pas valide",
        self::MALFORMED_BANQUE  => "Le code banque doit contenir 5 chiffres",
        self::MALFORMED_GUICHET => "Le code guichet doit contenir 5 chiffres",
        self::MALFORMED_COMPTE  => "Le numéro de compte doit contenir 11 chiffres ou lettres",
        self::MALFORMED_CLE     => "La clé doit contenir 2 chiffres",
    );

    /**
     * 
     * @return bool
     */
    public function isValid($value)
    {
        if (is_string($value)) {
            $value = array(
                'banque'  => substr($value, 0, 5),
                'guichet' => substr($value, 5, 5),
                'compte'  => substr($value, 10, 11),
                'cle'     => substr($value, -2),
            );
        }
//        if (!isset($value['banque']) || !isset($value['guichet']) || !isset($value['compte']) || !isset($value['cle'])) {
//            throw new LogicException("La valeur du numéro INSEE doit être un tableau possédant les clés 'banque', 'guichet', 'compte' et 'cle'.");
//        }
        
        $banque  = isset($value['banque']) ?  $value['banque'] :  null;
        $guichet = isset($value['guichet']) ? $value['guichet'] : null;
        $compte  = isset($value['compte']) ?  $value['compte'] :  null;
        $cle     = isset($value['cle']) ?     $value['cle'] :     null;
        
//        if (!$banque || mb_strlen($banque) !== 5) {
//            $this->error(self::MALFORMED_BANQUE);
//            return false;
//        }
//        if (!$guichet || mb_strlen($guichet) !== 5) {
//            $this->error(self::MALFORMED_BANQUE);
//            return false;
//        }
//        if (!$compte || mb_strlen($compte) !== 11) {
//            $this->error(self::MALFORMED_BANQUE);
//            return false;
//        }
//        if (!$cle || mb_strlen($cle) !== 2) {
//            $this->error(self::MALFORMED_CLE);
//            return false;
//        }
        
        $compte = strtr($compte, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', '12345678912345678923456789');
        
        $valid = 97 - bcmod(89 * $banque + 15 * $guichet + 3 * $compte, 97) === (int) $cle;
        
        if (!$valid) {
            $this->error(self::NOT_VALID);
            return false;
        }
        
        return true;
    }
}