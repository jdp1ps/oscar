<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-09-28 17:07
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;

use Doctrine\ORM\EntityManager;
use Oscar\Factory\LdapToPerson;

/**
 * Cette classe étend l'hydrateur de personne pour substituer une factory adaptée au LDAP
 *
 * Class LdapConnectorPersonHydrator
 * @package Oscar\Connector
 */
class LdapConnectorPersonHydrator extends ConnectorPersonHydrator
{
    /**
     * @param array $rolesMapping
     */
    public function setRolesMapping(array $rolesMapping)
    {
        $this->rolesMapping = $rolesMapping;
    }

    protected function factory()
    {
        static $factory;
        if ($factory === null) {
            $factory = new LdapToPerson($this->rolesMapping, $this->getOrganizationRepository());
        }
        return $factory;
    }
}
