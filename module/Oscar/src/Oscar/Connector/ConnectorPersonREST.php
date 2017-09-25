<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-30 14:58
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\Role;
use Oscar\Exception\ConnectorException;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceManager;

class ConnectorPersonREST implements IConnectorPerson, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait, ConnectorParametersTrait;

    private $editable = false;

    public function setEditable($editable){
        $this->editable = $editable;
    }

    public function isEditable(){
        return $this->editable;
    }

    function getName()
    {
        return "rest";
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

    public function init(ServiceManager $sm, $configFilePath)
    {
        $this->setServiceLocator($sm);
        $this->loadParameters($configFilePath);
    }

    /**
     * @return PersonRepository
     */
    public function getPersonRepository()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Person::class);
    }


    public function execute( $force = false)
    {
        $personRepository = $this->getPersonRepository();

        return $this->syncPersons($personRepository, $force);
    }

    /**
     * @param PersonRepository $personRepository
     * @param bool $force
     * @return ConnectorRepport
     * @throws \Oscar\Exception\OscarException
     */
    function syncPersons(PersonRepository $personRepository, $force)
    {
        $repport = new ConnectorRepport();

        $url = $this->getParameter('url_persons');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        $return = curl_exec($curl);

        if( false === $return ){
            // @todo Trouver un moyen de faire remonter une erreur plus "causante"
            $this->getServiceLocator()
                ->get('Logger')
                ->error(sprintf(
                    "Accès connector '%s' impossible : %s",
                    $this->getName(),
                    curl_error($curl)
                ));
            throw new ConnectorException(sprintf("Le connecteur %s n'a pas fournis les données attendues", $this->getName()));
        }
        curl_close($curl);

        foreach( json_decode($return) as $personData ){
            try {
                /** @var Person $personOscar */
                $personOscar = $personRepository->getPersonByConnectorID($this->getName(),
                    $personData->uid);
                $action = "update";
            } catch( NoResultException $e ){
                $personOscar = $personRepository->newPersistantPerson();
                $action = "add";
            } catch( NonUniqueResultException $e ){
                $repport->adderror(sprintf("La personne avec l'ID %s est en double dans oscar.", $personData->uid));
                continue;
            }
            if($personData->dateupdated == null
                || $personOscar->getDateSyncLdap() == null
                || $personOscar->getDateSyncLdap() < $personData->dateupdated
                || $force == true ){
                $personOscar = $this->hydratePersonWithDatas($personOscar, $personData);
                $personRepository->flush($personOscar);
                if( $action == 'add' ){
                    $repport->addadded(sprintf("%s a été ajouté.", $personOscar->log()));
                } else {
                    $repport->addupdated(sprintf("%s a été mis à jour.", $personOscar->log()));
                }
            } else {
                $repport->addnotice(sprintf("%s est à jour.", $personOscar->log()));
            }
        }
        return $repport;
    }

    protected function getRolesOscarByRoleId(){
        static $roles;
        if( $roles === null ){
            $roles = [];
            /** @var Role $role */
            foreach($this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Role::class)->findAll() as $role ){
                $roles[$role->getRoleId()] = $role;
            }
        }
        return $roles;
    }

    protected function getStructureByCode( $code ){
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Organization::class)->findOneBy(['code' => $code]);
    }

    private function hydratePersonWithDatas( Person $personOscar, $personData ){
        $rolesOscar = $this->getRolesOscarByRoleId();
        foreach( $personData->roles as $organizationCode=>$roles ){
            /** @var Organization $organization */
            $organization = $this->getStructureByCode($organizationCode);
            if( $organization ){
                foreach( $roles as $roleId ){
                    if( array_key_exists($roleId, $rolesOscar) ){
                        if( !$organization->hasPerson($personOscar, $roleId) ){
                            $roleOscar = new OrganizationPerson();
                            $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->persist($roleOscar);
                            $roleOscar->setPerson($personOscar)
                                ->setOrganization($organization)
                                ->setRoleObj($rolesOscar[$roleId]);
                            $personOscar->getOrganizations()->add($roleOscar);

                        }
                    }
                }
            }
        }

        return $personOscar->setConnectorID($this->getName(), $personData->uid)
            ->setLadapLogin($personData->login)
            ->setFirstname($personData->firstname)
            ->setLastname($personData->lastname)
            ->setEmail($personData->mail)
            ->setHarpegeINM($personData->inm)
            ->setPhone($personData->phone)
            ->setDateSyncLdap(new \DateTime())
            ->setLdapStatus($personData->status)
            ->setLdapAffectation($personData->affectation)
            ->setLdapSiteLocation($personData->structure)
            ->setLdapMemberOf($personData->groups);
    }

    function syncPerson(Person $person)
    {
        if ($person->getConnectorID($this->getName())) {

            $url = sprintf($this->getParameter('url_person'), $person->getConnectorID($this->getName()));
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_COOKIESESSION, true);
            $return = curl_exec($curl);
            curl_close($curl);
            if( false === $return ){
                // @todo Trouver un moyen de faire remonter une erreur plus "causante"
                throw new ConnectorException(sprintf("Le connecteur %s n'a pas fournis les données attendues", $this->getName()));
            }

            $personData = json_decode($return);
            if( $personData === null ){
                // @todo Trouver un moyen de faire remonter une erreur plus "causante"
                throw new ConnectorException(sprintf("Aucune données retournée par le connecteur%s.", $this->getName()));
            }

            return $this->hydratePersonWithDatas($person, $personData);

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