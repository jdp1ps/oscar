<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/10/15 15:47
 * @copyright Certic (c) 2015
 */

namespace Oscar\Validator;


use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class EOTP extends AbstractValidator
{
    const NOT_EOTP = 'notEotp';
    const REGEX_EOTP = "/^[0-9]{3}[A-Z]{2}[0-9]{2,4}$/mi";

    protected $messageTemplates = array(
        self::NOT_EOTP => "'%value%' n'est pas un EOTP valide."
    );

    public function isValid($value)
    {
        $this->setValue($value);

        if( !preg_match(self::REGEX_EOTP, $value) ) {
            $this->error(self::NOT_EOTP);
            return false;
        }

        return true;
    }
}