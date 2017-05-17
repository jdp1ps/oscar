<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-05-11 12:25
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;

use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Exception\OscarException;

/**
 * Cette classe permet de synchroniser les rôles des personnes dans les
 * organisation.
 *
 * Class ConnectorPersonOrganization
 * @package Oscar\Connector
 */
abstract class AbstractConnectorPersonOrganization
{
    ////////////////////////////////////////////////////////////// CONFIGURATION

    /**
     * @var string Nom du connecteur utilisé pour retrouver la personne (ID)
     */
    private $personConnector;

    /**
     * @var string Nom du connecteur utilisé pour retrouver l'organisation
     */
    private $organizationConnector;

    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * @var OrganizationRepository
     */
    private $organizationRepository;

    /**
     * @var array
     */
    private $rolesConnection;

    private $editable = false;

    public function setEditable($editable){
        $this->editable = $editable;
    }

    public function isEditable(){
        return $this->editable;
    }

    /**
     * ConnectorPersonOrganization constructor.
     * @param string $personConnector
     * @param string $organizationConnector
     * @param PersonRepository $personRepository
     * @param OrganizationRepository $organizationRepository
     */
    public function __construct(
        $personConnector,
        $organizationConnector,
        PersonRepository $personRepository,
        OrganizationRepository $organizationRepository,
        $rolesConnection
    ) {
        $this->personConnector = $personConnector;
        $this->organizationConnector = $organizationConnector;
        $this->personRepository = $personRepository;
        $this->organizationRepository = $organizationRepository;
        $this->rolesConnection = $rolesConnection;
    }



    /**
     * @return string
     */
    public function getPersonConnector()
    {
        return $this->personConnector;
    }

    /**
     * @param string $personConnector
     */
    public function setPersonConnector($personConnector)
    {
        $this->personConnector = $personConnector;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationConnector()
    {
        return $this->organizationConnector;
    }

    /**
     * @param string $organizationConnector
     */
    public function setOrganizationConnector($organizationConnector)
    {
        $this->organizationConnector = $organizationConnector;

        return $this;
    }

    /**
     * @return PersonRepository
     */
    public function getPersonRepository()
    {
        return $this->personRepository;
    }

    /**
     * @param PersonRepository $personRepository
     */
    public function setPersonRepository($personRepository)
    {
        $this->personRepository = $personRepository;

        return $this;
    }

    /**
     * @return OrganizationRepository
     */
    public function getOrganizationRepository()
    {
        return $this->organizationRepository;
    }

    /**
     * @param OrganizationRepository $organizationRepository
     */
    public function setOrganizationRepository($organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;

        return $this;
    }


    /**
     * @param Person $person
     */
    public function synchronizePerson( Person $person )
    {
        // Récupération de l'identifiant de la personne
        $personConnectorId = $person->getConnectorID($this->getPersonConnector());

        if( !$personConnectorId ){
            throw new OscarException(sprintf(
                "La personne '%s' n'a pas d'identifiant pour le connecteur '%s'",
                $person,
                $this->getPersonConnector())
            );
        }

        $affectationsInConector = $this->getConnectorPersonAffectations($personConnectorId);

        var_dump($this->rolesConnection);
        echo "<hr>";
        foreach( $affectationsInConector as $organizationId => $roles ){
            var_dump($organizationId);
            var_dump($roles);
        }
    }

    /**
     * Retourne un tableau de donnée sous la forme :
     * [
     *      IDORGANISATION => ['ROLE1', 'ROLE2', 'ROLEN'],
     *      IDORGANISATION => ['ROLE1', 'ROLE2', 'ROLEN']
     * ]
     * @param $personId
     */
    abstract function getConnectorPersonAffectations( $personId );



}