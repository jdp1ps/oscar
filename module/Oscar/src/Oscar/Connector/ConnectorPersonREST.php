<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-30 14:58
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Connector\DataAccessStrategy\HttpBasicStrategy;
use Oscar\Connector\DataAccessStrategy\IDataAccessStrategy;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Exception\ConnectorException;
use Oscar\Exception\OscarException;

class ConnectorPersonREST extends AbstractConnector
{
    public function getPathAll(): string
    {
        return $this->getParameter('url_persons');
    }

    public function getPathSingle($remoteId): string
    {
        return sprintf($this->getParameter('url_person'), $remoteId);
    }

    private $editable = false;
    private $options;

    /** @var  ConnectorPersonHydrator */
    private $personHydrator = null;

    public function setEditable($editable){
        $this->editable = $editable;
    }

    public function isEditable(){
        return $this->editable;
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

    public function execute( $force = false)
    {
        $personRepository = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Person::class);

        return $this->syncPersons($personRepository, $force);
    }

    /**
     * @return ConnectorPersonHydrator
     */
    public function getPersonHydrator()
    {
        if( $this->personHydrator === null ){
            $this->personHydrator = new ConnectorPersonHydrator(
                $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')
            );
            $this->personHydrator->setPurge($this->getOptionPurge());
        }
        return $this->personHydrator;
    }

    protected function log( string $text ) :void {
        if( true ){
            echo "$text";
        }
    }

    /**
     * @param PersonRepository $personRepository
     * @param bool $force
     * @return ConnectorRepport
     * @throws \Oscar\Exception\OscarException
     */
    function syncPersons(PersonRepository $personRepository, $force)
    {
        $exist = $personRepository->getUidsConnector($this->getName());
        $repport = new ConnectorRepport();
        $this->getPersonHydrator()->setPurge($this->getOptionPurge());
        $repport->addnotice(sprintf("Il y'a déjà %s personne(s) synchronisée(s) pour le connector '%s'", count($exist), $this->getName()));
        $access = $this->getAccessStrategy();
        $this->log("Pending access : " . count($exist));

        try {
            $json = $access->getDataAll();
            $this->log("data gain : " . count($json));
            $personsDatas = null;

            if( is_object($json) && property_exists($json, 'persons') ){
                $personsDatas = $json->persons;
            } else {
                $personsDatas = $json;
            }

            if( !is_array($personsDatas) ){
                throw new \Exception("L'API n'a pas retourné un tableau de donnée");
            }
            $nbrPersonsConnector = count($personsDatas);
            $repport->addnotice(count($personsDatas). " résultat(s) a traiter.");
            ////////////////////////////////////

            foreach( $personsDatas as $personData ){

                if( ! property_exists($personData, 'uid') ){
                    $repport->addwarning(sprintf("Les donnèes %s n'ont pas d'UID.", print_r($personData, true)));
                    continue;
                }

                if( $this->getOptionPurge() ){
                    $uid = $personData->uid;
                    if( ($index = array_search($uid, $exist)) >= 0 ){
                        array_splice($exist, $index, 1);
                    }
                }

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
                } catch(\Exception $e){
                    // FIX : Erreur de conversion de type survenue
                    $repport->adderror(sprintf("La personne avec l'ID %s provoque une exception : %s - %s.", $personData->uid, $e->getMessage(), $e->getTraceAsString()));
                    continue;
                }

                if( $personData->dateupdated == null
                    || $personOscar->getDateSyncLdap() == null
                    || $personOscar->getDateSyncLdap()->format('Y-m-d') < $personData->dateupdated
                    || $force == true )
                {
                    $personOscar = $this->getPersonHydrator()->hydratePerson($personOscar, $personData, $this->getName());

                    $repport->addRepport($this->getPersonHydrator()->getRepport());

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

            if( $this->getOptionPurge() ){

                $idsToDelete = [];

                foreach ($exist as $uid){
                    try {
                        /** @var Person $personOscarToDelete */
                        $personOscarToDelete = $personRepository->getPersonByConnectorID($this->getName(), $uid);

                        $activeIn = [];

                        if( count($personOscarToDelete->getActivities()) > 0 ){
                            $activeIn[] = "activité";
                        }

                        if( count($personOscarToDelete->getProjectAffectations()) > 0 ){
                            $activeIn[] = "projet";
                        }

                        // Récupération des affectations issues de la synchro
                        $synchronizedAffectationsOrganizations = $personOscarToDelete->getOrganizationsSync();
                        if( count($synchronizedAffectationsOrganizations) > 0 ){
                            $personRepository->removeOrganizationPersons($synchronizedAffectationsOrganizations);
                            $personRepository->flush($synchronizedAffectationsOrganizations);
                        }

                        if( count($personOscarToDelete->getOrganizations()) > 0 ){
                            $activeIn[] = "organisation";
                        }


                        if( count($activeIn) == 0 ){
                            $idsToDelete[] = $personOscarToDelete->getId();
                        } else {

                            // Tentative de suppression des rôles synchronisés



                            $repport->addwarning("$personOscarToDelete n'a pas été supprimé car il est actif dans : " . implode(', ', $activeIn));
                        }

                    } catch (\Exception $e){
                        $repport->adderror("$personOscarToDelete n'a pas été supprimé car il est utilisé dans oscar : " . $e->getMessage());
                    }
                }

                foreach ($idsToDelete as $idPerson) {
                    try {
                        $personRepository->removePersonById($idPerson);
                        $repport->addremoved("Suppression de person $idPerson : ");
                    } catch (\Exception $e) {
                        $repport->adderror("Immpossible de suprimer la person $idPerson : " . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e ){
            $this->log("ERROR : " . $e->getMessage());
            throw new \Exception("Impossible de synchroniser les personnes : " . $e->getMessage());
        }

        $this->log("FLUSH...");
        $personRepository->flush(null);
        $this->log("FLUSH DONE...");

        return $repport;
    }

    function syncPerson(Person $person)
    {
        if ($person->getConnectorID($this->getName())) {
            $personIdRemote = $person->getConnectorID($this->getName());

            try {
                $personData = $this->getAccessStrategy()->getDataSingle($personIdRemote);
            } catch (\Exception $e) {
                $msg = "Aucune données de correspondance pour la personne '$personIdRemote' : " . $e->getMessage();
                $this->getLogger()->error($msg);
                throw new OscarException($msg);
            }

            // Fix : Nouveau format
            if( property_exists($personData, 'error_code') ){
                switch($personData->error_code){
                    case 'PERSON_DISABLED':
                        $person->disabledLdapNow();
                        $this->getLogger()->info("'$person' a été désactivée");
                        return $person;
                    default:
                        throw new OscarException("Code d'erreur '".$personData->error_code."' inconnu");

                }
                $personData = $personData->person;
            }

            // Fix : Nouveau format
            if( property_exists($personData, 'person') ){
                $personData = $personData->person;
            }

            return $this->getPersonHydrator()->hydratePerson($person, $personData, $this->getName());

        } else {
            $msg = 'Impossible de synchroniser la personne ' . $person;
            $this->getLogger()->error($msg);
            throw new \Exception($msg);
        }
    }


}