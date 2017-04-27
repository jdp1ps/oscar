<?php

namespace UnicaenApp\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Description of NumeroINSEE
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NumeroINSEE extends AbstractValidator
{
    const MALFORMED            = 'malformed';
    const MALFORMED_PROVISOIRE = 'malformedProvisoire';
    const NOT_VALID            = 'notValid';
    const NOT_VALID_PROVISOIRE = 'notValidProvisoire';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::MALFORMED            => "Un numéro INSEE est composé de 13 caractères et d'une clé de contrôle de 2 caractères",
        self::MALFORMED_PROVISOIRE => "Un numéro INSEE provisoire est composé de 13 caractères (pas de clé de contrôle)",
        self::NOT_VALID            => "Le numéro INSEE n'est pas valide",
        self::NOT_VALID_PROVISOIRE => "Le numéro INSEE provisoire n'est pas valide",
    );
    protected $options = array();
    
    /**
     * 
     * @return bool
     */
    public function isValid($value)
    {
        // validation différente pour les numéros INSEE provisoires
        if ($this->getProvisoire()) {
            return $this->isValidProvisoire($value);
        }
        
        if (is_string($value)) {
            $value = array(
                'numero' => substr($value, 0, 13),
                'cle'    => substr($value, 13, 2),
            );
        }
        
        $numero = isset($value['numero']) ? $value['numero'] : null;
        $cle    = isset($value['cle']) ?    $value['cle'] :    null;
        
        if (!$numero || strlen($numero) !== 13) {
            $this->error(self::MALFORMED);
            return false;
        }
        
        if (!$cle || strlen($cle) !== 2) {
            $this->error(self::MALFORMED);
            return false;
        }
        
        $numero = (int) str_replace(array('A', 'B'), '0', $numero);
        $cle    = (int) $cle;
        $reste  = bcmod($numero, 97);
        
        $valid = (97 - $reste) === $cle;
        
        if (!$valid) {
            $this->error(self::NOT_VALID);
            return false;
        }
        
        return true;
    }
    
    /**
     * Validation d'un numéro INSEE provisoire.
     * NB: pas de clé.
     * 
     * @param string $value
     */
    private function isValidProvisoire($value)
    {
        if (!$value /*|| strlen($value) !== 13*/) {
            $this->error(self::MALFORMED_PROVISOIRE);
            return false;
        }
        
//        // Format :
//        // - 1 ou 2, 
//        // - année de naissance sur 2 chiffres, 
//        // - mois de naissance sur 2 chiffres, 
//        // - département de naissance sur 2 chiffres, 
//        // - pays de naissance sur 3 chiffres, 
//        // - 1 lettre (première lettre du nom de famille)
//        // - 2 chiffres (a priori "01" systématiquement)
//        $pattern = "^(1|2)\d{2}\d{2}\d{2}\d{3}[a-zA-Z]\d{2}$";
//        
//        if (!preg_match("`$pattern`", $value)) {
//            $this->error(self::NOT_VALID_PROVISOIRE);
//            return false;
//        }
        
        return true;
    }
    
    /**
     * Retourne la valeur de l'option "provisoire".
     * 
     * @return boolean
     */
    protected function getProvisoire()
    {
        return $this->getOption('provisoire');
    }
}