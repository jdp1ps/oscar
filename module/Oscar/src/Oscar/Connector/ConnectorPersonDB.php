<?php

namespace Oscar\Connector;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Exception\OscarException;

class ConnectorPersonDB extends AbstractConnector
{

    /** @var  ConnectorPersonHydrator */
    private $personHydrator = null;

    public function execute($force = false)
    {
        $personRepository = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->getRepository(Person::class);

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
        $exist = $personRepository->getUidsConnector($this->getName());
        $repport = new ConnectorRepport();
        $this->getPersonHydrator()->setPurge($this->getOptionPurge());
        $repport->addnotice(
            sprintf(
                "Il y'a déjà %s personne(s) synchronisée(s) pour le connector '%s'",
                count($exist),
                $this->getName()
            )
        );
        $access = $this->getAccessStrategy();
        $this->log("Pending access : " . count($exist));

        try {
            $rows = $access->getDataAll($this->mapParams());

            $personsDatas = [];
            foreach( $rows as $row ){
                $personsDatas[] = $this->objectFromDBRow($row);
            }

            $this->log("data gain : " . count($personsDatas));

            $repport->addnotice(count($personsDatas) . " résultat(s) a traiter.");
            ////////////////////////////////////

            foreach ($personsDatas as $personData) {
                if (!property_exists($personData, 'uid')) {
                    $repport->addwarning(sprintf("Les donnèes %s n'ont pas d'UID.", print_r($personData, true)));
                    continue;
                }

                $uid = $personData->uid;
                if (($index = array_search($uid, $exist)) >= 0) {
                    array_splice($exist, $index, 1);
                }

                try {
                    /** @var Person $personOscar */
                    $personOscar = $personRepository->getPersonByConnectorID(
                        $this->getName(),
                        $personData->uid
                    );
                    $action = "update";
                } catch (NoResultException $e) {
                    $personOscar = $personRepository->newPersistantPerson();
                    $action = "add";
                } catch (NonUniqueResultException $e) {
                    $repport->adderror(sprintf("La personne avec l'ID %s est en double dans oscar.", $personData->uid));
                    continue;
                } catch (\Exception $e) {
                    // FIX : Erreur de conversion de type survenue
                    $repport->adderror(
                        sprintf(
                            "La personne avec l'ID %s provoque une exception : %s - %s.",
                            $personData->uid,
                            $e->getMessage(),
                            $e->getTraceAsString()
                        )
                    );
                    continue;
                }

                if ($personData->dateupdated == null
                    || $personOscar->getDateSyncLdap() == null
                    || $personOscar->getDateSyncLdap()->format('Y-m-d') < $personData->dateupdated
                    || $force == true) {
                    $personOscar = $this->getPersonHydrator()->hydratePerson(
                        $personOscar,
                        $personData,
                        $this->getName()
                    );

                    $repport->addRepport($this->getPersonHydrator()->getRepport());

                    $personRepository->flush($personOscar);

                    if ($action == 'add') {
                        $repport->addadded(sprintf("%s a été ajouté.", $personOscar->log()));
                    } else {
                        $repport->addupdated(sprintf("%s a été mis à jour.", $personOscar->log()));
                    }
                } else {
                    $repport->addnotice(sprintf("%s est à jour.", $personOscar->log()));
                }
            }


            $idsToDelete = [];

            foreach ($exist as $uid) {
                if (!$uid) {
                    continue;
                }

                try {
                    /** @var Person $personOscarToDelete */
                    $personOscarToDelete = $personRepository->getPersonByConnectorID($this->getName(), $uid);
                    $personOscarToDelete->disabledLdapNow();

                    if ($this->getOptionPurge()) {
                        $activeIn = [];

                        if (count($personOscarToDelete->getWorkPackages()) > 0) {
                            $activeIn[] = "feuille de temps";
                        }

                        if (count($personOscarToDelete->getActivities()) > 0) {
                            $activeIn[] = "activité";
                        }

                        if (count($personOscarToDelete->getProjectAffectations()) > 0) {
                            $activeIn[] = "projet";
                        }

                        // Récupération des affectations issues de la synchro
                        $synchronizedAffectationsOrganizations = $personOscarToDelete->getOrganizationsSync();
                        if (count($synchronizedAffectationsOrganizations) > 0) {
                            $personRepository->removeOrganizationPersons($synchronizedAffectationsOrganizations);
                            $personRepository->flush($synchronizedAffectationsOrganizations);
                        }

                        if (count($personOscarToDelete->getOrganizations()) > 0) {
                            $activeIn[] = "organisation";
                        }


                        if (count($activeIn) == 0) {
                            $idsToDelete[] = $personOscarToDelete->getId();
                        } else {
                            // Tentative de suppression des rôles synchronisés


                            $repport->addwarning(
                                "$personOscarToDelete n'a pas été supprimé car il est actif dans : " . implode(
                                    ', ',
                                    $activeIn
                                )
                            );
                        }
                    }
                } catch (\Exception $e) {
                    $repport->adderror(
                        "$personOscarToDelete n'a pas été supprimé car il est utilisé dans oscar : " . $e->getMessage()
                    );
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
        } catch (\Exception $e) {
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
                $row = $this->getAccessStrategy()->getDataSingle($personIdRemote, $this->mapParams());
            } catch (\Exception $e) {
                $msg = "Aucune données de correspondance pour la personne '$personIdRemote' : " . $e->getMessage();
                $this->getLogger()->error($msg);
                throw new OscarException($msg);
            }

            $personData = $this->objectFromDBRow($row);            

            return $this->getPersonHydrator()->hydratePerson($person, $personData, $this->getName());
        } else {
            $msg = 'Impossible de synchroniser la personne ' . $person;
            $this->getLogger()->error($msg);
            throw new \Exception($msg);
        }
    }

    private function mapParams() {
        $dbParam = [];
        $dbParam['db_host'] = $this->getParameter('db_host');
        $dbParam['db_port'] = $this->getParameter('db_port');
        $dbParam['db_user'] = $this->getParameter('db_user');
        $dbParam['db_password'] = $this->getParameter('db_password');
        $dbParam['db_name'] = $this->getParameter('db_name');
        $dbParam['db_charset'] = $this->getParameter('db_charset');
        $dbParam['db_query_all'] = $this->getParameter('db_query_all');
        $dbParam['db_query_single'] = $this->getParameter('db_query_single');
        return $dbParam;
    }

    private function objectFromDBRow($row) {
        $person = new \stdClass();

        $person->uid = $row['REMOTE_ID'];
        $person->login = $row['LOGIN'];
        $person->firstname = $row['PRENOM'];
        $person->lastname = $row['NOM'];
        $person->mail = $row['EMAIL'];
        $person->civilite = $row['CIVILITE'];
        $person->preferedlanguage = $row['LANGAGE'];
        $person->status = $row['STATUT'];
        $person->affectation = $row['AFFECTATION'];
        $person->inm = $row['INM'];
        $person->phone = $row['TELEPHONE'];
        $person->datefininscription = $row['DATE_EXPIRATION'];
        $person->datecreated = NULL;
        $person->dateupdated = NULL;
        $person->datecached = NULL;

        $person->address = NULL;
        if ($row['ADRESSE_PROFESSIONNELLE'] != NULL) {
            $person->address = json_decode($row['ADRESSE_PROFESSIONNELLE']);
        }

        $roles_json = $row['ROLES'];
        $oscar_roles = new \stdClass();
        if ($roles_json != NULL) {
            $roles = json_decode($roles_json);
            foreach ($roles as $key => $values) {
                $oscar_role_for_structure = [];
                foreach ($values as $value) {
                    if ($value == 'D30') {
                        $oscar_role_for_structure[] = 'Directeur de composante';
                    } else if ($value == 'R00') {
                        $oscar_role_for_structure[] = 'Responsable';
                    } else if ($value == 'R40') {
                        $oscar_role_for_structure[] = 'Directeur de composante';
                    } else if ($value == 'P50') {
                        $oscar_role_for_structure[] = 'Directeur de composante';
                    } else if ($value == 'T87') {
                        $oscar_role_for_structure[] = 'Informaticien';
                    } else if ($value == 'T98') {
                        $oscar_role_for_structure[] = 'Gestionnaire de laboratoire';
                    } else if ($value == 'A009') {
                        $oscar_role_for_structure[] = 'Gestion financière';
                    } else if ($value == 'Gestionnaire financière des contrats de recherche') {
                        $oscar_role_for_structure[] = 'Gestion financière';
                    } else if ($value == 'Gestionnaire financiere des contrats de recherche') {
                        $oscar_role_for_structure[] = 'Gestion financière';
                    } else if ($value == 'Directrice') {
                        $oscar_role_for_structure[] = 'Directeur';
                    } else if ($value == 'Directeur adjoint') {
                        $oscar_role_for_structure[] = 'Directeur';
                    } else if ($value == 'Directrice adjointe') {
                        $oscar_role_for_structure[] = 'Directeur';
                    } else if ($value == 'Responsable administrative') {
                        $oscar_role_for_structure[] = 'Responsable administratif';
                    } else {
                        $oscar_role_for_structure[] = $value;
                    }
                }
                $oscar_roles->$key = $oscar_role_for_structure;
            }
        }

        $person->roles = $oscar_roles;

        return $person;
    }

    protected function log(string $text): void
    {
        if (true) {
            echo "$text";
        }
    }

    /**
     * @return ConnectorPersonHydrator
     */
    public function getPersonHydrator()
    {
        if ($this->personHydrator === null) {
            $this->personHydrator = new ConnectorPersonHydrator(
                $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')
            );
            $this->personHydrator->setPurge($this->getOptionPurge());
        }
        return $this->personHydrator;
    }

    function getRemoteID()
    {
        return "REMOTE_ID";
    }
    
    function getRemoteFieldname($oscarFieldName)
    {

    }

    public function getPathAll(): string
    {
        return $this->getParameter('url_persons');
    }

    public function getPathSingle($remoteId): string
    {
        return sprintf($this->getParameter('url_person'), $remoteId);
    }

    public function logError($msg) {
        $this->getLogger()->error($msg);
    }

    public function checkAccess()
    {
        parent::checkAccess();

        $rows = $this->getAccessStrategy()->getDataAll($this->mapParams());
        if (!is_array($rows)) {
            throw new \Exception("Le connecteur PersonDB n'a pas retourné un tableau de donnée");
        }
        $this->log(" (" . \count($rows) . " personnes trouvées en DB) ");
        return true;
    }
}
