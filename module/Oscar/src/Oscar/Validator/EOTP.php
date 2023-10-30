<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/10/15 15:47
 * @copyright Certic (c) 2015
 */

namespace Oscar\Validator;


use Laminas\Validator\AbstractValidator;

class EOTP extends AbstractValidator
{
    const NOTEOTP = 'noteotp';
    const REGEX_EOTP = "/^[0-9]{3}[A-Z]{2,3}[0-9]{2,4}$/mi";

    private $eotpRegex;

    protected $messageTemplates = array(
        self::NOTEOTP => "'%value%' n'est pas un EOTP valide."
    );

    /**
     * EOTP constructor.
     * @param $eotpRegex
     */
    public function __construct($eotpRegex=null)
    {
        if( $eotpRegex === null )
            $this->eotpRegex = self::REGEX_EOTP;
        else
            $this->eotpRegex = $eotpRegex;

        parent::__construct(null);

    }


    public function isValid($value)
    {
        $this->setValue($value);

        if( !preg_match($this->eotpRegex, $value) ) {
            $this->error(self::NOTEOTP);
            return false;
        }

        return true;
    }
}