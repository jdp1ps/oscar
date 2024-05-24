<?php
/**
 * @author Joachim Dornbusch<joachim.dornbusch@univ-paris1.fr>
 * @date: 2024-05-17
 */

namespace Oscar\Connector\Access;

use Oscar\Connector\IConnector;
use UnicaenApp\Mapper\Ldap\AbstractMapper;
use UnicaenApp\Options\ModuleOptions;
use Zend\Ldap\Ldap;

/**
 * Accès aux données via le client LDAP
 *
 * Class AbstractLdapAccessConnector
 * @package Oscar\Connector\Access
 */
abstract class AbstractLdapAccessConnector implements IConnectorAccess
{

    /** @var IConnector */
    private $connector;

    /** @var ModuleOptions */
    private $options;

    /** @var array */
    protected $filters;


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

    /**
     * Uniquement pour les tests check:config
     *
     * @return array
     */
    public function getFirstData(): array
    {
        $mapper = $this->getLdapMapper();
        return $mapper->searchFirstEntry();
    }

    public function getConnector(): IConnector
    {
        return $this->connector;
    }

    public function getDataSingle($remoteId, $params = null)
    {
        $mapper = $this->getLdapMapper();
        $data = $mapper->findByCode($remoteId);
        if (count($data) > 1) {
            throw new \Exception(
                "L'annuaire LDAP a retourné plusieurs entités pour l'identifiant " . $remoteId
            );
        }
        if (empty($data)) {
            throw new \Exception(
                "L'annuaire LDAP n'a pas retourné d'entité pour l'identifiant " . $remoteId
            );
        }
        return $this->ensureUid($this->convertDates($this->castToObjects($data)))[0];
    }

    public function getDataAll($params = null)
    {
        $data = [];
        foreach ($this->connector->getFilters() as $filter) {
            $data = array_merge($data, $this->getDatas($filter));
        }
        return $this->ensureUid($this->convertDates($this->castToObjects($data)));
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
        $ldapConnectorClass = $this->getMapperClass();
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

    /**
     * @param array $data
     * @return object[]
     */
    public function convertDates(array $data): array
    {
        return array_map(function ($entry) {
            $entry->dateupdated = $this->convertTimeStamp($entry->modifytimestamp);
            return $entry;
        }, $data);
    }

    /**
     * @param array $data
     * @return object[]
     */
    public function ensureUid(array $data): array
    {
        return array_map(function ($entry) {
            if(!property_exists($entry, 'uid')) {
                $entry->uid = $entry->supanncodeentite;
            }
            return $entry;
        }, $data);
    }

    /**
     * Convertit un timestamp LDAP en objet DateTime
     *
     * @param string|null $timestamp
     * @return \DateTime
     */
    public function convertTimeStamp(string $timestamp = null): \DateTime
    {
        $date = \DateTime::createFromFormat('YmdHis',
            substr($timestamp, 0, 14),
            new \DateTimeZone('UTC')
        );

        if (!isset($date) || !$date instanceof \DateTime) {
            $date = new \DateTime();
        }
        return $date;
    }

    /**
     * @return string
     */
    abstract protected function getMapperClass(): string;
}
