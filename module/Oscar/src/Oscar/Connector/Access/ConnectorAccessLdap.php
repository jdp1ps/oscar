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
use Zend\Ldap\Ldap;

/**
 * Accès aux données via le client LDAP
 *
 * Class ConnectorAccessCurlHttp
 * @package Oscar\Connector\Access
 */
class ConnectorAccessLdap implements IConnectorAccess
{

    /** @var IConnector */
    private $connector;

    /** @var ModuleOptions */
    private $options;

    /** @var array */
    private $filters;


    /**
     * ConnectorAccessCurlHttp constructor.
     * @param IConnector $connector Connector qui va consommer l'accès aux données.
     */
    public function __construct(IConnector $connector)
    {
        $this->connector = $connector;
    }


    public function getDatas($filter): array
    {
        $mapper = $this->getLdapMapper();
        return $mapper->findByCategoryFilter($filter);
    }

    public function getConnector(): IConnector
    {
        return $this->connector;
    }

    public function getDataSingle($remoteId, $params = null)
    {
        $mapper = $this->getLdapMapper();
        $data = $mapper->findByCode($remoteId);
        return $this->castToObjects($data);
    }

    public function getDataAll($params = null)
    {
        $data = [];
        foreach ($this->connector->getFilters() as $filter) {
            $data = array_merge($data, $this->getDatas($filter));
        }
        return $this->castToObjects($data);
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
    }


    private function getLdapMapper(): AbstractMapper
    {
        $ldapConnectorClass = OrganizationLdap::class;
        $configLdap = $this->options->getLdap();
        $ldap = $configLdap['connection']['default']['params'];
        $ldapConnector = new $ldapConnectorClass;
        $ldapConnector->setConfig($configLdap);
        $ldapConnector->setLdap(new Ldap($ldap));
        return $ldapConnector;
    }

    /**
     * @param array $data
     * @return object[]
     */
    public function castToObjects(array $data): array
    {
        return array_map(function ($entry) {
            return (object)$entry;
        }, $data);
    }
}
