<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-05-11 12:25
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;

use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Exception\OscarException;
use Symfony\Component\Yaml\Yaml;
use UnicaenApp\Mapper\Ldap\People;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\ServiceManager;

/**
 * Cette classe permet de synchroniser les rôles des personnes dans les
 * organisation.
 *
 * Class ConnectorPersonOrganization
 * @package Oscar\Connector
 */
class ConnectorPersonOrganizationLdap extends AbstractConnectorPersonOrganization
{
    /**
     * Service UnicaenApp pour obtenir les affectations.
     *
     * @var People
     */
    private $ldapPeople;

    private $paramsFile;

    private $params;

    /**
     * @return People
     */
    public function getLdapPeople()
    {
        return $this->ldapPeople;
    }

    /**
     * ConnectorPersonOrganization constructor.
     * @param string $personConnector
     * @param string $organizationConnector
     * @param PersonRepository $personRepository
     * @param OrganizationRepository $organizationRepository
     */
    public function __construct() {

    }

    public function init( ServiceManager $sm, $configFilePath){
        $this->setOrganizationConnector('ldap');
        $this->setPersonConnector('ldap');
        $this->setOrganizationRepository($sm->get('Doctrine\ORM\EntityManager')->getRepository(Organization::class));
        $this->setPersonRepository($sm->get('Doctrine\ORM\EntityManager')->getRepository(Person::class));
        $this->setParamsFile($configFilePath);
    }

    public function setParamsFile( $filepath )
    {
        $this->paramsFile = $filepath;
        $yml = new \Symfony\Component\Yaml\Parser();
        $this->params = $yml->parse(file_get_contents(APP_DIR.$filepath));
    }

    public function getConnectorPersonAffectations( $personId )
    {
        $personDatas = $this->getLdapPeople()->findOneByUid($personId);
        $affectationsDatas = $personDatas->getSupannRolesEntiteToArray();
        $return = [];
        foreach( $affectationsDatas as $affectation ){
            $organizationId = $affectation['code'];
            if( !array_key_exists($organizationId, $return) ){
                $return[$organizationId] = [];
            }
            $role = $affectation['role'];
            $return[$organizationId][] = $role;
        }
        return $return;
    }

    public function syncAll(){
        $persons = $this->getPersonRepository()->findAll();
        die("Synchro de tout = " . count($persons));
    }

    public function getType(){
        return $this->getConfigData()['type'];
    }


    public function getConfigData(){
        return [
            'type' => 'person_organization_ldap',
            'label' => 'Affectation des personnes aux organisations via LDAP',
            'class' => self::class,
            'rolesOscar' => $this->getRolesOscar(),
            'warnings' => [],
            'file' => $this->paramsFile,
            'params' => $this->params
        ];
    }

    public function updateParameters( $datas ){
        $config = $this->getConfigData();
        $params = $this->params;
        foreach($datas as $key => $value){
            if( array_key_exists($key, $params) ){
                $this->params[$key]['value'] = $value;
                $config['params'][$key]['value'] = $value;
            }
        }
        $ymlWriter = new Yaml();
        file_put_contents(APP_DIR.$config['file'], $ymlWriter->dump( $config['params']));
    }

    public function getRolesOscar() {
        return $this->getPersonRepository()->getRolesOrganizationArray();
    }
}