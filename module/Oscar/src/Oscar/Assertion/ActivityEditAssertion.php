<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 08/02/16 16:53
 * @copyright Certic (c) 2016
 */

namespace Oscar\Assertion;


use UnicaenAuth\Assertion\AbstractAssertion;
use Zend\Permissions\Acl\Resource\ResourceInterface;

class ActivityEditAssertion extends AbstractAssertion
{
    protected function assertEntity( ResourceInterface $entity = null, $privilege = null ){
        die('ASSERTION !!!');
    }

}