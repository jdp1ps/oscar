<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-23 15:42
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Symfony\Component\Yaml\Yaml;
use UnicaenApp\Mapper\Ldap\Structure;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class ConnectorOrganizationLdap implements IConnectorOrganization, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    /** @var  Structure */
    private $ldapStructureService;

    private $configFilePath;

    const LDAP_FILTER_ALL = '*';

    public function __construct(){}

    public function init( ServiceManager $sm, $configFilePath){
        $this->setServiceLocator($sm);
        $this->configFilePath = $configFilePath;
    }

    /**
     * @return OrganizationRepository
     */
    public function getOrganizationRepository(){
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Organization::class);
    }


    public function execute(){
        $organizationRepository = $this->getOrganizationRepository();
        return $this->syncOrganizations($organizationRepository, true);
    }


    function getOrganizationData($idConnector)
    {
        // TODO: Implement getOrganizationData() method.
    }

    public function getConfigData()
    {
        return [
            'type' => 'organization_ldap',
            'label' => 'Synchronisation des organisation depuis LDAP',
            'class' => self::class,
            'warnings' => [],
            'file' => $this->configFilePath,
            'params' => $this->getParams()
        ];
    }

    public function updateParameters($data){
        if( $this->configFilePath ){
            $yml = new \Symfony\Component\Yaml\Parser();
            $params = $yml->parse(file_get_contents($this->configFilePath));
            foreach( $params as $key=>$oldData ){
                if( array_key_exists($key, $data) ){
                    $params[$key]['value'] = $data[$key];
                }
                elseif( $data['type'] == 'keykey' ){
                    $params[$key]['value'] = [];
                }
            }
            file_put_contents($this->configFilePath, Yaml::dump($params));
        } else {
            die("Aucun fichier de configuration");
        }
    }

    public function getParams(){
        if( $this->configFilePath ){
            $yml = new \Symfony\Component\Yaml\Parser();
            $this->params = $yml->parse(file_get_contents($this->configFilePath));

            return $this->params; //Yaml::parse(file_get_contents($this->configFilePath));
        } else {
            return [];
        }
    }

    function syncOrganizations(OrganizationRepository $repository, $force)
    {
        $this->ldapStructureService = $this->getServiceLocator()->get('ldap_structure_service')->getMapper();

        $repport = [
            "errors"    => [],
            "warnings"  => [],
            "infos"     => [],
            "notices"   => [],
        ];

        $datas = $this->ldapStructureService->findAllByCodeStructure(self::LDAP_FILTER_ALL);
        $correspondancesType = $this->getParams()['relations']['value'];

        /** @var \UnicaenApp\Entity\Ldap\Structure $data */
        foreach( $datas as $data ){

            $type = str_replace('{SUPANN}', '', $data->getSupannTypeEntite());
            if( $correspondancesType && array_key_exists($type, $correspondancesType) ){
                $type = array_search($correspondancesType[$type], Organization::getTypesSelect());
            } else {
                $type = "";
            }

            $split = explode('$', $data->getPostaladdress());

            $code = $data->getSupannCodeEntite();

            $ldapData = [
                'shortName'  => $data->getOu(),
                'fullName'  => $data->getDescription(),
                'phone'  => $data->getTelephoneNumber(),
                'ldapSupannCodeEntite'  => $data->getSupannCodeEntite(),
                'country'   => 'country',
                'street1'   => $split[0],
                'street2'   => $split[1],
                'street3'   => $split[2],
                'zipCode'   => $split[3],
                'city'   => $split[4],
                'country'   => $split[5],
                'code'  => str_replace('HS_', '', $data->getSupannCodeEntite()),
                'type' => $type
            ];

            /** @var Organization $organization */
            $organization = null;
            $type = "notices";

            try {
                $organization = $repository->getObjectByConnectorID($this->getName(), $code);
                $organization->setDateUpdated(new \DateTime());
                $type = "notices";
            } catch( NoResultException $ex ){
                try {
                    $organization = $repository->getOrganisationByCode($ldapData['code']);
                    $type = "infos";
                } catch( NoResultException $ex ){

                } catch( NonUniqueResultException $ex ){
                    $repport['errors'] = 'Doublon détecté sur le code LDAP ' . $ldapData['code'];
                    continue;
                }
            } catch( NonUniqueResultException $ex ){
                $repport['errors'] = 'Doublon détecté sur le ConnectorID ' . $code;
                continue;
            }

            if( $organization == null ){
                $organization = $repository->newPersistantObject();
                $organization->setDateCreated(new \DateTime());
                $type = "infos";
            }

            foreach( $ldapData as $method=>$value ){
                $set = 'set'. ucfirst($method);
                $organization->$set($value);
            }

            $organization->setConnectorID($this->getName(), $data->getSupannCodeEntite());

            $repository->flush($organization);
            $repport[$type][] = ($type == 'notices' ? 'Mise à jour de ' : 'Création de ') . (string)$organization;
        }

        return $repport;
    }

    function getName() {
        return 'ldap';
    }

    private function getUsedType( $typeLdap ){
        static $correspondancesType;
        if( $correspondancesType === null ){
            $correspondancesType = $this->getServiceLocator()->get('Config')['oscar']['connectors']['config'];
        }

        return array_key_exists($typeLdap, $correspondancesType) ? $correspondancesType[$typeLdap] : null;
    }

    function syncOrganization(Organization $organization)
    {
        $report = [];

        /////////////////////////////////////////////// Récupération des données
        /** @var Structure ldapStructureService */
        $this->ldapStructureService = $this->getServiceLocator()->get('ldap_structure_service')->getMapper();

        $codeLDAP = $organization->getConnectorID($this->getName());
        $data = $this->ldapStructureService->findOneByDnOrCodeEntite($codeLDAP);
        if( !$data ){
            throw new \Exception(sprintf("Impossible d'obtenir les données LDAP pour l'organisation %s", $codeLDAP));
        }


        return $this->hydrateOragnisationWithLdapDatas($organization, $data);
    }

    protected function hydrateOragnisationWithLdapDatas( Organization $organization, $data ){
        $split = explode('$', $data->getPostaladdress());
        $type = str_replace('{SUPANN}', '', $data->getSupannTypeEntite());
        $typeOscar = $this->getUsedType($type);

        $ldapData = [
            'shortName'  => $data->getOu(),
            'fullName'  => $data->getDescription(),
            'phone'  => $data->getTelephoneNumber(),
            'ldapSupannCodeEntite'  => $data->getSupannCodeEntite(),
            'country'   => 'country',
            'street1'   => $split[0],
            'street2'   => $split[1],
            'street3'   => $split[2],
            'zipCode'   => $split[3],
            'city'   => $split[4],
            'country'   => $split[5],
            'code'  => str_replace('HS_', '', $data->getSupannCodeEntite()),
            'type' => $typeOscar
        ];

        foreach( $ldapData as $method=>$value ){
            $set = 'set'. ucfirst($method);
            $organization->$set($value);
        }

        return $organization;
    }
}