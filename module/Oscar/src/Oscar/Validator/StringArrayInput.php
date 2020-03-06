<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 12/02/20
 * Time: 11:50
 */

namespace Oscar\Validator;


use Oscar\Exception\OscarException;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class StringArrayInput extends AbstractValidator
{
    private $allowEmpty;

    /**
     * StringArrayInput constructor.
     * @param $allowEmpty
     */
    public function __construct($allowEmpty = true)
    {
        $this->allowEmpty = $allowEmpty;
    }


    public function isValid($value)
    {
        // TODO: Implement isValid() method.
    }
}