<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 23/07/15
 * Time: 11:58
 */

namespace UnicaenAppTest\Message\Specification;


use UnicaenApp\Message\Specification\MessageSpecificationInterface;

class ByRoleSpecificationTestAsset implements MessageSpecificationInterface
{
    private $role;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function isSatisfiedBy($context = null, array &$sentBackData = [])
    {
        return isset($context['role']) && $context['role'] === $this->role;
    }
}