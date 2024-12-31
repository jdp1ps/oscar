<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Connector\Access;

use Oscar\Connector\IConnector;
use Oscar\Mapper\Ldap\OrganizationLdap;
use UnicaenApp\Mapper\Ldap\AbstractMapper;
use UnicaenApp\Options\ModuleOptions;
use Laminas\Ldap\Ldap;

/**
 * Accès aux données des organisations via le client LDAP
 *
 * Class OrganizationConnectorAccessLdap
 * @package Oscar\Connector\Access
 */
class OrganizationConnectorAccessLdap extends AbstractLdapAccessConnector
{

    /**
     * @return string
     */
    protected function getMapperClass(): string
    {
        return OrganizationLdap::class;
    }
}
