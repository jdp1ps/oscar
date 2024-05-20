<?php
/**
 * @author Joachim Dornbusch <joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Connector;

use Oscar\Connector\Access\IConnectorAccess;

class ConnectorPersonLDAP extends ConnectorPersonREST
{

    protected function customizeStrategy(IConnectorAccess $accessStrategy)
    {
        $accessStrategy->setOptions($this->getServicemanager()->get('unicaen-app_module_options'));
    }

    protected function getHydratorClass()
    {
        return LdapConnectorPersonHydrator::class;
    }


    protected function customizeHydrator($hydrator)
    {
        $hydrator->setRolesMapping($this->getParameter('mapping_role_person')[0]);
    }

    public function getFilters()
    {
        return $this->getParameter('person_ldap_filters');
    }

}
