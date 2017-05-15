<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-05-11 12:25
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;

use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
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

    const LDAP_PERSONS = '(&(eduPersonAffiliation=member)(!(eduPersonaffiliation=student)))';
    const STAFF_ACTIVE_OR_DISABLED = 'ou=people,dc=unicaen,dc=fr';
    const REGEX_SUPANN_ROLE = '/\[role=\{SUPANN\}(\w*)\].*\[libelle=(.*)\]/';

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
        $this->ldapPeople = $sm->get('ldap_people_service')->getMapper();
    }

    public function setParamsFile( $filepath )
    {
        $this->paramsFile = $filepath;
        $yml = new \Symfony\Component\Yaml\Parser();
        $this->params = $yml->parse(file_get_contents($filepath));
    }


    /**
     * Retourne la liste des rôles disponibles dans le connector sur la forme :
     * [
     *  'CODE1' => 'Libelle 1',
     *  'CODE2' => 'Libelle 2',
     *  'CODEn' => 'Libelle n'
     * ]
     */
    public function getRolesRemoteAvailables(){
        $personsLDAP = $this->getLdapPeople()->searchSimplifiedEntries(
            self::LDAP_PERSONS,
            self::STAFF_ACTIVE_OR_DISABLED,
            [],
            'cn'
        );
        $re = self::REGEX_SUPANN_ROLE;

        $rolesGross = [];

        foreach($personsLDAP as $person ){
            if( array_key_exists('supannroleentite', $person) ){
                if( is_string($person['supannroleentite']) ){
                    $supannRoleEntite = [$person['supannroleentite']];
                } else {
                    $supannRoleEntite = $person['supannroleentite'];
                }

                // traitement
                foreach( $supannRoleEntite as $supannRole ){
                    if( FALSE !== preg_match($re, $supannRole, $matches) ) {
                        if( count($matches) == 3 && !array_key_exists($matches[1], $rolesGross ) ){
                            $rolesGross[$matches[1]] = $matches[2];
                        }
                    }
                }
            }
        }
        return $rolesGross;
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

    public function execute(){

        $personsOscar = $this->getPersonRepository()->findAll();
        $config = $this->getConfigData();
        $repport = [
            'errors'    => [],
            'notices'   => [],
            'warnings'  => [],
            'infos'     => []
        ];

        $correspondance = $this->params['relations']['value'];
        $rolesOscar = $this->getRolesOscar();

        /** @var Person $person */
        foreach($personsOscar as $person){



            // Récupération de l'ID de la personne
            $connectorPersonID = $person->getConnectorID($config['personConnectorKeyName']);

            if( !$connectorPersonID ){
                $repport['warnings'][] =  "$person n'est pas connecté à LDAP";
                continue;
            }

            // Récupération des données distantes
            /** @var \UnicaenApp\Entity\Ldap\People $personDatas */
            $personDatas = $this->getLdapPeople()->findOneByUid($connectorPersonID);

            if( $personDatas ){

                // La personne a des rôles fixées dans le connector
                if( $personDatas->getSupannRolesEntiteToArray() ){
                    foreach( $personDatas->getSupannRolesEntiteToArray() as $supannRole ){
                        $codeRole = $supannRole['role'];
                        $codeEtab = $supannRole['code'];
                        if( !array_key_exists($codeRole, $correspondance) ){
                            $message = sprintf("Le rôle (code = %s) n'a pas de correspondance dans Oscar.", $codeRole);
                            if( !in_array($message, $repport['notices']) ){
                                $repport['notices'][] = $message;
                            }
                        } else {
                            $roleOscarId = $correspondance[$codeRole];
                            $roleOscar = $rolesOscar[$roleOscarId];

                            try {
                                /** @var Organization $organisation */
                                $organisation = $this->getOrganizationRepository()->getObjectByConnectorID($config['organizationConnectorKeyName'], $codeEtab);
                                    var_dump($roleOscar);

                                if( !$organisation->hasPerson($person, $roleOscar) ){
                                    $this->getOrganizationRepository()->saveOrganizationPerson($person, $organisation, $roleOscarId);
                                    $repport['infos'][] = "Ajout du rôle $roleOscar a $person dans $organisation";
                                } else {
                                    $repport['notice'][] = "$person a déjà le rôle $roleOscar dans $organisation";
                                }
                            } catch ( \Exception $e ){
                                $repport['errors'][] = "L'organisation $codeEtab est absent de Oscar";
                            }

                        }
                    }

                } else {
                    $repport['notices'][] =  "$person n'a pas de rôle pris en charge dans LDAP";
                }
            } else {
                $repport['error'][] =  "Impossible de charger les données pour $person ($connectorPersonID).";
            }
        }


        var_dump($repport);
        die('EXEC !');
    }

    public function syncAll(){
        $persons = $this->getPersonRepository()->findAll();
        die("Synchro de tout = " . count($persons));
    }

    public function getType(){
        return $this->getConfigData()['type'];
    }


    public function getConfigData( $loadRoles = false ){
        return [
            'type' => 'person_organization_ldap',
            'label' => 'Affectation des personnes aux organisations via LDAP',
            'personConnectorKeyName' => 'ldap',
            'organizationConnectorKeyName' => 'ldap',
            'class' => self::class,
            'rolesOscar' => $loadRoles ? $this->getRolesOscar() : null,
            'rolesRemote' => $loadRoles ? $this->getRolesRemoteAvailables(): null,//$this->getRolesRemoteAvailables(),
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
        file_put_contents($config['file'], $ymlWriter->dump( $config['params']));
    }

    public function getRolesOscar() {
        return $this->getPersonRepository()->getRolesOrganizationArray();
    }
}