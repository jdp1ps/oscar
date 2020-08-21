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
use Oscar\Exception\ConnectorException;

class ConnectorPersonREST extends AbstractConnector implements IConnectorPerson
{

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

    /**
     * @param PersonRepository $personRepository
     * @param bool $force
     * @return ConnectorRepport
     * @throws \Oscar\Exception\OscarException
     */
    function syncPersons(PersonRepository $personRepository, $force)
    {
        $exist = [];
        $exist = $personRepository->getUidsConnector($this->getName());
        $repport = new ConnectorRepport();
        $this->getPersonHydrator()->setPurge($this->getOptionPurge());
        $repport->addnotice(sprintf("Il y'a déjà %s personne(s) synchronisée(s) pour le connector '%s'", count($exist), $this->getName()));
        $access = $this->getAccessStrategy($this->getParameter('url_persons'));

        try {
            $json = $access->getDatas();
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
                        if( count($personOscarToDelete->getOrganizations()) > 0 ){
                            $activeIn[] = "organisation";
                        }

                        if( count($activeIn) == 0 ){
                            $idsToDelete[] = $personOscarToDelete->getId();
                        } else {
                            $repport->addwarning("$personOscarToDelete n'a pas été supprimé car il est actif dans : " . implode(', ', $activeIn));
                        }

                    } catch (\Exception $e){
                        $repport->adderror("$personOscarToDelete n'a pas été supprimé car il est actif dans les activités : " . $e->getMessage());
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
            throw new \Exception("Impossible de synchroniser les personnes : " . $e->getMessage());
        }

        $personRepository->flush(null);

        return $repport;
    }

    function syncPerson(Person $person)
    {
        if ($person->getConnectorID($this->getName())) {

            $personIdRemote = $person->getConnectorID($this->getName());
            $url = sprintf($this->getParameter('url_person'), $personIdRemote);
            $this->getLogger()->info("connector request : " . $url);
            $access = $this->getAccessStrategy($url);
            $personData = $access->getDatas($personIdRemote);

            // Fix : Nouveau format
            if( property_exists($personData, 'person') ){
                $personData = $personData->person;
            }

            return $this->getPersonHydrator()->hydratePerson($person, $personData, $this->getName());

        } else {
            throw new \Exception('Impossible de synchroniser la personne ' . $person);
        }
    }
}