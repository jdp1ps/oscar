<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Connector;

use Oscar\Connector\Access\IConnectorAccess;
use Oscar\Entity\Organization;
use Oscar\Factory\LdapToOrganization;

class ConnectorOrganizationLDAP extends ConnectorOrganizationREST
{
    /**
     * @return LdapToOrganization
     */
    protected function factory()
    {
        static $factory;
        if ($factory === null) {
            $types = $this->getRepository()->getTypesKeyLabel();
            $typeMappings = $this->getParameter("organisation_types");
            $addressParser = $this->getParameter("address_parser");
            $factory = new LdapToOrganization($types, $typeMappings, $addressParser);
        }
        return $factory;
    }

    public function getFilters()
    {
        return $this->getParameter('organisation_ldap_filters');
    }

    private function hydrateWithDatas(Organization $organization, $data)
    {
        return $this->factory()->hydrateWithDatas($organization, $data, $this->getName());
    }

    protected function customizeStrategy(IConnectorAccess $access)
    {
        $access->setOptions($this->getServicemanager()->get('unicaen-app_module_options'));

    }

    /**
     * Uniquement pour les tests check:config
     *
     * @return bool
     */
    public function checkAccess(): bool
    {
        $datas = $this->getAccessStrategy()->getFirstData();
        if ($datas) {
            return true;
        }
        return false;
    }
}
