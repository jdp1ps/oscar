<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Connector\Access;


use Oscar\Mapper\Ldap\PersonLdap;

/**
 * Accès aux données des personnes via le client LDAP
 *
 * Class PersonConnectorAccessLdap
 * @package Oscar\Connector\Access
 */
class PersonConnectorAccessLdap extends AbstractLdapAccessConnector
{

    /**
     * @return string
     */
    protected function getMapperClass(): string
    {
        return PersonLdap::class;
    }
}
