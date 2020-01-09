<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/20
 * Time: 10:52
 */

namespace Oscar\Connector;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Connector\DataAccessStrategy\HttpAuthBasicStrategy;
use Oscar\Connector\DataExtractionStrategy\DataExtractionStringToJsonStrategy;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;

class ConnectorPersonJsonHttpAuthBasic extends AbstractConnectorOscar
{
    private $personHydrator;

    function execute($force = true)
    {
        $dataAccessStrategy         = new HttpAuthBasicStrategy($this);
        $dataExtractionStrategy     = new DataExtractionStringToJsonStrategy();
        $report                     = new ConnectorRepport();

        try {
            $url = $this->getParameter('url_persons');
        } catch (\Exception $e) {
            $report->adderror("Erreur de configuration : " . $e->getMessage());
            return $report;
        }


        $msg = sprintf(_("Chargement des données depuis '%s'"), $url);

        // Récupération des données
        try {
            $datas = $dataAccessStrategy->getData($url);
            $report->addnotice("$msg : OK ( ". strlen($datas) ." chars extract)");
        } catch (\Exception $e) {
            $report->adderror("$msg : ERROR (" . $e->getMessage() . ")");
            throw new \Exception("Impossible de charger des données depuis $url : " . $e->getMessage());
        }

        // Conversion
        $msg = sprintf(_("Conversion des données"), $url);
        try {
            $json = $dataExtractionStrategy->extract($datas);
            // Autorise la présence d'une clef 'persons' au premier niveau (facultatif)
            if( is_object($json) && property_exists($json, 'persons') ){
                $personsDatas = $json->persons;
            } else {
                $personsDatas = $json;
            }
        } catch (\Exception $e) {
            $report->adderror("$msg : ERROR (" . $e->getMessage() . ")");
            throw new \Exception("Impossible de convertir les données depuis $url : " . $e->getMessage());
        }

        if( !is_array($personsDatas) ){
            throw new \Exception("L'API n'a pas retourné un tableau de donnée");
        }
        $nbrPersonsConnector = count($personsDatas);
        $report->addnotice(count($personsDatas). " résultat(s) a traiter.");

        // ...
        $this->syncPersons($personsDatas, $this->getPersonRepository(), $report, true);

        return $report;
    }

    /**
     * @return ConnectorPersonHydrator
     */
    public function getPersonHydrator()
    {
        if( $this->personHydrator === null ){
            $this->personHydrator = new ConnectorPersonHydrator(
                $this->getEntityManager()
            );
            $this->personHydrator->setPurge($this->getOptionPurge());
        }
        return $this->personHydrator;
    }

    /**
     * @return PersonRepository
     */
    public function getPersonRepository(){
        return $this->getEntityManager()->getRepository(Person::class);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(){
        return $this->getServiceManager()->get('Doctrine\ORM\EntityManager');
    }


    public function syncPersons($personsDatas, PersonRepository $personRepository, ConnectorRepport &$repport, $force) {


        if( $this->getOptionPurge() ){
            $exist = $personRepository->getUidsConnector($this->getName());
            $repport->addnotice(sprintf(_("Il y'a %s personne(s) référencées dans Oscar."), count($exist)));
        }

        $this->getPersonHydrator()->setPurge($this->getOptionPurge());


        $nbrPersonsConnector        = 0;
        $nbrPersonsOscar            = count($exist);
        $nbrPersonsDeleted          = 0;
        $nbrPersonsUseAndDeletable  = 0;



        /////////////////////////////////////
        ////// Patch 2.7 "Lewis" GIT#286 ////
        try {

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
                    $personOscar = $personRepository->getPersonByConnectorID($this->getName(), $personData->uid);
                    $action = "update";

                } catch( NoResultException $e ){
                    $personOscar = $personRepository->newPersistantPerson();
                    $action = "add";

                } catch( NonUniqueResultException $e ){
                    $repport->adderror(sprintf("La personne avec l'ID %s est en double dans oscar.", $personData->uid));
                    continue;
                }

                if( $personData->dateupdated == null
                    || $personOscar->getDateSyncLdap() == null
                    || $personOscar->getDateSyncLdap()->format('Y-m-d') < $personData->dateupdated
                    || $force == true )
                {
                    $personOscar = $this->getPersonHydrator()->hydratePerson($personOscar, $personData, $this->getName());
                    if( $personOscar == null ){
                        throw new \Exception("WTF $action");
                    }

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


}