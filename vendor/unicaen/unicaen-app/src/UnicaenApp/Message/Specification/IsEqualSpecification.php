<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 23/07/15
 * Time: 11:48
 */

namespace UnicaenApp\Message\Specification;

/**
 * Classe de specification qui teste simplement si le contexte égale (===) une valeur précise.
 *
 * @package UnicaenApp\Message
 */
class IsEqualSpecification implements MessageSpecificationInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function isSatisfiedBy($context = null, array &$sentBackData = [])
    {
        return $context === $this->value;
    }

    public function getValue()
    {
        return $this->value;
    }
}