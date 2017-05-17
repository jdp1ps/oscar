<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-30 14:58
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use UnicaenApp\Mapper\Ldap\People;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;

class ConnectorPersonLDAP implements IConnectorPerson, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const LDAP_PERSONS = '(&(eduPersonAffiliation=member)(!(eduPersonaffiliation=student)))';
    const STAFF_ACTIVE_OR_DISABLED = 'ou=people,dc=unicaen,dc=fr';

    private $editable = false;

    public function setEditable($editable){
        $this->editable = $editable;
    }

    public function isEditable(){
        return $this->editable;
    }

    function getName()
    {
        return "ldap";
    }

    function getRemoteID()
    {
        return "uid";
    }

    function getRemoteFieldname($oscarFieldName)
    {
        // TODO: Implement getRemoteFieldname() method.
    }

    function getPersonData($idConnector)
    {
        // TODO: Implement getPersonData() method.
    }

    public function getConfigData()
    {
        return null;
    }

    /**
     * @param Person $person
     * @param $ldapData
     * @return Person
     */
    protected function hydratePersonWithData(Person $person, $ldapData)
    {

        $email = $ldapData['mail'];

        if (array_key_exists('memberof', $ldapData)) {
            $filters = [];
            if (is_array($ldapData['memberof'])) {
                $filters = $ldapData['memberof'];
            } else {
                if (is_string($ldapData['memberof'])) {
                    $filters[] = $ldapData['memberof'];
                }
            }
            $person->setLdapMemberOf($filters);
        }

        $resupannaffectation = '/\w*;(.*)/i';

        if (!array_key_exists('supannaffectation', $ldapData)) {
            $supannaffectation = "";
        } else {
            $supannaffectation = $ldapData['supannaffectation'];

            if (is_array($supannaffectation)) {
                $supannaffectation = $supannaffectation[0];
            }

            preg_match($resupannaffectation, $supannaffectation, $matches);

            if (count($matches) >= 2) {
                $supannaffectation = $matches[1];
            }
        }

        $supannlocation = "";
        if (array_key_exists('ucbnsitelocalisation', $ldapData)) {
            $supannlocation = $ldapData['ucbnsitelocalisation'];

            if (is_array($supannlocation)) {
                $supannlocation = $supannlocation[0];
            }

            preg_match($resupannaffectation, $supannlocation, $matches);

            if (count($matches) >= 2) {
                $supannlocation = $matches[1];
            }
        }

        if (array_key_exists('ucbnsousstructure', $ldapData)) {
            $supannlocation = $ldapData['ucbnsousstructure'];

            if (is_array($supannlocation)) {
                $supannlocation = $supannlocation[0];
            }

            preg_match($resupannaffectation, $supannlocation, $matches);

            if (count($matches) >= 2) {
                $supannlocation = $matches[1];
            }
        }

        if (array_key_exists('ucbnstatus', $ldapData)) {
            $person->setLdapStatus($ldapData['ucbnstatus']);
        }
        /****
         * if( array_key_exists('datefininscription', $ldapData) ){
         * //            $person->setLdapFinInscription(createDanew \DateTime()$ldapData['datefininscription']);
         * }
         * /****/

        $connectorId = $ldapData[$this->getRemoteID()];
        $firstName = $ldapData['givenname'];
        $lastName = is_array($ldapData['sn']) ? $ldapData['sn'][0] : $ldapData['sn'];
        $login = $ldapData['supannaliaslogin'];

        $person->setConnectorID($this->getName(), $connectorId)
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setDateUpdated(new \DateTime())
            ->setEmail($email)
            ->setLdapAffectation($supannaffectation)
            ->setLdapSiteLocation($supannlocation)
            ->setLadapLogin($login);

        return $person;

    }

    public function init(ServiceManager $sm, $configFilePath)
    {
        $this->setServiceLocator($sm);
    }

    /**
     * @return PersonRepository
     */
    public function getPersonRepository()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Person::class);
    }


    public function execute()
    {
        $personRepository = $this->getPersonRepository();

        return $this->syncPersons($personRepository, true);
    }

    function syncPersons(PersonRepository $personRepository, $force)
    {
        $personsLDAP = $this->getServiceLdap()->searchSimplifiedEntries(
            self::LDAP_PERSONS,
            self::STAFF_ACTIVE_OR_DISABLED,
            [],
            'cn'
        );

        $repport = [
            "errors" => [],
            "warnings" => [],
            "infos" => [],
            "notices" => [],
        ];

        foreach ($personsLDAP as $p) {


            $email = null;
            if (!key_exists('mail', $p)) {
                $connectorId = $p[$this->getRemoteID()];
                $fullname = $p['givenname'] . (is_array($p['sn']) ? $p['sn'][0] : $p['sn']);
                $repport['errors'][] = sprintf("Impossible d'importer %s(%s), pas d'email",
                    $fullname, $connectorId);
                continue;
            }

            $email = $p['mail'];
            $type = 'infos';
            $persons = $personRepository->getPersonsByConnectorID('ldap',
                $p[$this->getRemoteID()]);

            // On recherche avec l'email au cas ou
            if (count($persons) == 0) {
                $persons = $personRepository->getPersonByEmail($email);
            }

            if (count($persons) == 0) {
                $person = $personRepository->newPersistantObject();
            } else {
                if (count($persons) == 1) {
                    $person = $persons[0];
                } else {
                    $repport['errors'][] = sprintf("Doublons détéctés pour %s.",
                        $persons[0]);
                    continue;
                }
            }


            if ($person) {
                $type = 'update';
            } else {
                $type = 'create';
                $person = $personRepository->newPersistantObject();
            }

            $action = $type == 'update' ? 'mis à jour' : 'ajouté';

            try {
                $person = $this->hydratePersonWithData($person, $p);
                $personRepository->flush($person);

                $repportType = $type == 'update' ? 'notices' : 'infos';
                $repport[$repportType][] = sprintf('%s (%s) a été %s',
                    $person->getDisplayName(), $person->getEmail(), $action);

            } catch (\Exception $e) {
                $repport['errors'][] = sprintf("Error %s (%s) n'a pas été %s",
                    $person->getDisplayName(), $person->getEmail(), $action);
            }
        }

        return $repport;
    }

    function syncPerson(Person $person)
    {
        if ($person->getConnectorID($this->getName())) {
            $filter = sprintf(People::UID_FILTER,
                $person->getConnectorID($this->getName()));
            $entry = $this->getServiceLdap()->searchSimplifiedEntry($filter,
                People::UTILISATEURS_BASE_DN);
            if ($entry) {
                return $this->hydratePersonWithData($person, $entry);
            } else {
                throw new \Exception(sprintf("%s(%s) n'est plus présent(e) dans LDAP.",
                    $person, $person->getConnectorID($this->getName())));
            }

        } else {
            throw new \Exception('Impossible de synchroniser la personne ' . $person);
        }

    }

    /**
     * @return \UnicaenApp\Mapper\Ldap\People
     */
    protected function getServiceLdap()
    {
        return $this->getServiceLocator()->get('ldap_people_service')->getMapper();
    }
}