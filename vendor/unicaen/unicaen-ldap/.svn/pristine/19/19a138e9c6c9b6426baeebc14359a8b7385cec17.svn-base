<?php

namespace UnicaenLdap;

use Zend\Ldap\Dn;
use Zend\Ldap\Node as ZendNode;

/**
 * Noeud Ldap
 *
 * @author Laurent Lécluse <laurent.lecluse at unicaen.fr>
 */
class Node extends ZendNode {

    /**
     * Factory method to create an attached Zend\Ldap\Node for a given DN.
     *
     * @param  string|array|Dn $dn
     * @param  Ldap            $ldap
     * @return Node|null
     * @throws Exception\LdapException
     */
    public static function fromEntry($dn, Ldap $ldap, array $data)
    {
        if (is_string($dn) || is_array($dn)) {
            $dn = Dn::factory($dn);
        } elseif ($dn instanceof Dn) {
            $dn = clone $dn;
        } else {
            throw new Exception(null, '$dn is of a wrong data type.');
        }
        $entry = new static($dn, $data, true, $ldap);
        return $entry;
    }
}