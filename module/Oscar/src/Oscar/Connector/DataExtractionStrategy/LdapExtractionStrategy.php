<?php
/**
 * Created by PhpStorm.
 * User: Sisomolida HING
 * Date: 14/02/24
 * Time: 13:34
 */

namespace Oscar\Connector\DataExtractionStrategy;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Connector\ConnectorPersonHydrator;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\PersonLdap;
use Oscar\Entity\PersonRepository;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zend\Ldap\Ldap;
use Zend\ServiceManager\ServiceManager;

class LdapExtractionStrategy
{
    private ConnectorPersonHydrator $hydratorPerson;
    private bool $purge = false;
    private array $mappingRolePerson = array(
        //ID 21 correspond au role "Directeur de laboratoire" en base de donnée
        "{UAI:0751717J:HARPEGE.FCSTR}530" => 21
    );
    private ServiceManager $servicemanager;

    public function __construct(ServiceManager $sm)
    {
        $this->servicemanager = $sm;
    }

    public function initiateLdapPerson($configLdap, $ldap): PersonLdap
    {
        $ldapConnector = new PersonLdap();
        $ldapConnector->setConfig($configLdap);
        $ldapConnector->setLdap(new Ldap($ldap));

        return $ldapConnector;
    }

    public function parseLdapPerson($person): array
    {
        $person['firstname'] = isset($person['givenname']) ? $person['givenname'] : "";
        $person['lastname'] = isset($person['sn']) ? $person['sn'] : "";
        $person['login'] = isset($person['uid']) ? $person['uid'] : "";
        $person['codeHarpege'] = isset($person['supannentiteaffectationprincipale'])? $person['supannentiteaffectationprincipale'] : "" ;

        if(isset($person['mail'])){
            $person['email'] = $person['mail'];
            $person['emailPrive'] = $person['mail'];
        } else {
            if(isset($person['edupersonprincipalname'])) {
                $person['email'] = $person['edupersonprincipalname'];
                $person['emailPrive'] = $person['edupersonprincipalname'];
            }
        }

        $person['phone'] = isset($person['telephonenumber']) ? $person['telephonenumber'] : "" ;
        $person['projectAffectations'] = $person['edupersonaffiliation'];
        $person['ldapsitelocation'] = isset($person['buildingName']) ? $person['buildingName']: null;

        if(isset($person["supannroleentite"])){
            $person['supannroleentite'] = $person["supannroleentite"];
        }

        if(isset($person['supannentiteaffectation']) && is_array($person['supannentiteaffectation'])){


            $nbAffectation = count($person['supannentiteaffectation']);
            $nbTmp = 0;
            $person['affectation'] = "";
            $person['organizations'] = array();

            foreach($person['supannentiteaffectation'] as $affectation){
                $person['affectation'] .= $affectation;
                $nbTmp++;

                if($nbTmp < $nbAffectation){
                    $person['affectation'] .= ',';
                }
            }
        } else {
            //OrganizationPerson
            if(isset($person['supannentiteaffectation'])) {
                $person['affectation'] = $person['supannentiteaffectation'];
            }
        }

        $person['activities'] = null;
        $person['ladapLogin'] = $person['supannaliaslogin'];
        $person['dateupdated'] = null;

        return $person;
    }

    public function syncPersons($personsData, $personRepository, object &$logger): void
    {
        $this->writeLog($logger,count($personsData). " résultat(s) reçus vont être traité.");

        if( $this->purge ){
            $exist = $personRepository->getUidsConnector($this->getName());
            $this->writeLog($logger, sprintf(_("Il y'a %s personne(s) référencées dans Oscar pour le connecteur '%s'."), count($exist), $this->getName()));
        }

        try {

            foreach( $personsData as $personData ){

                if( ! property_exists($personData, 'uid') ){
                    $this->writeLog($logger,sprintf("Les donnèes %s n'ont pas d'UID.", print_r($personData, true)), "error");
                    continue;
                }

                try {
                    /** @var Person $personOscar */
                    $personOscar = $personRepository->getPersonByConnectorID('ldap', $personData->uid);
                    $action = "update";

                } catch( NoResultException $e ){
                    $personOscar = $personRepository->newPersistantPerson();
                    $action = "add";

                } catch( NonUniqueResultException $e ){
                    $this->writeLog($logger,sprintf("La personne avec l'ID %s est en double dans oscar.", $personData->uid), "error");
                    continue;
                }

                if( $personData->dateupdated == null
                    || $personOscar->getDateSyncLdap() == null
                    || $personOscar->getDateSyncLdap()->format('Y-m-d') < $personData->dateupdated)
                {
                    $personOscar = $this->getPersonHydrate()->hydratePerson($personOscar, $personData, 'ldap');

                    if( $personOscar == null ){
                        $this->writeLog($logger,"WTF $action", "error");
                    }

                    if(isset($personData->supannroleentite)){
                        $rolesPerson = $personData->supannroleentite;
                        $organizationRepository = $this->getOrganizationRepository();

                        if(is_array($rolesPerson)){
                            foreach($rolesPerson as $role){
                                $substringRole = substr($role, 1, strlen($role)-2);
                                $explodeRole = explode("][",$substringRole);
                                $exactRole = substr($explodeRole[0],5,strlen($explodeRole[0]));
                                $exactCode = substr($explodeRole[2],5,strlen($explodeRole[2]));

                                if(array_key_exists($exactRole, $this->mappingRolePerson)){
                                    $dataOrg = $organizationRepository->getOrganisationByCodeNullResult($exactCode);
                                    $dataOrgPer = $organizationRepository->getOrganisationPersonByPersonNullResult($personOscar);

                                    if($dataOrg != null){
                                        if($dataOrgPer == null){
                                            $dataOrgPer = new OrganizationPerson();
                                        }

                                        $organizationRepository->saveOrganizationPerson(
                                            $dataOrgPer,
                                            $personOscar,
                                            $dataOrg,
                                            $this->mappingRolePerson[$exactRole]
                                        );
                                    }
                                }
                            }
                        } else {
                            $substringRole = substr($rolesPerson, 1, strlen($rolesPerson)-2);
                            $explodeRole = explode("][",$substringRole);
                            $exactRole = substr($explodeRole[0],5,strlen($explodeRole[0]));
                            $exactCode = substr($explodeRole[2],5,strlen($explodeRole[2]));

                            if(array_key_exists($exactRole, $this->mappingRolePerson)){
                                $dataOrg = $organizationRepository->getOrganisationByCodeNullResult($exactCode);
                                $dataOrgPer = $organizationRepository->getOrganisationPersonByPersonNullResult($personOscar);

                                if($dataOrg != null){
                                    if($dataOrgPer == null){
                                        $dataOrgPer = new OrganizationPerson();
                                    }

                                    $organizationRepository->saveOrganizationPerson(
                                        $dataOrgPer,
                                        $personOscar,
                                        $dataOrg,
                                        $this->mappingRolePerson[$exactRole]
                                    );
                                }
                            }
                        }


                    }
                    $personRepository->flush($personOscar);

                    if( $action == 'add' ){
                        $this->writeLog($logger,sprintf("%s a été ajouté.", $personOscar->log()));
                    } else {
                        $this->writeLog($logger,sprintf("%s a été mis à jour.", $personOscar->log()));
                    }
                } else {
                    $this->writeLog($logger,sprintf("%s est à jour.", $personOscar->log()));
                }
            }

            $nbPersons = count($personsData);
            $this->writeLog($logger,"$nbPersons personnes ont été ajouté(s) ou mise(s) à jour");

        } catch (\Exception $e ){
            $this->writeLog($logger,"Impossible de synchroniser les personnes : " . $e->getMessage(), "error");
        }

        $personRepository->flush(null);
    }

    private function writeLog($objectLog, $message, $typeLog = "write"){
        if(get_class($objectLog) == "Symfony\Component\Console\Style\SymfonyStyle"){
            if($typeLog == "write"){
                $objectLog->writeln($message);
            }

            if($typeLog == "error"){
                $objectLog->error($message);
            }
        }

        if(get_class($objectLog) == "Oscar\Connector\ConnectorRepport"){
            if($typeLog == "write"){
                $objectLog->addnotice($message);
            }

            if($typeLog == "error"){
                $objectLog->addwarning($message);
            }
        }
    }

    public function getOrganizationRepository(){
        return $this->getEntityManager()->getRepository(Organization::class);
    }

    public function getPersonHydrate(): ConnectorPersonHydrator
    {
        $this->hydratorPerson = new ConnectorPersonHydrator(
            $this->getEntityManager()
        );
        $this->hydratorPerson->setPurge($this->purge);

        return $this->hydratorPerson;
    }

    private function getServiceManager(): ServiceManager
    {
        return $this->servicemanager;
    }

    public function getEntityManager(){
        return $this->getServiceManager()->get(EntityManager::class);
    }
}