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
use UnicaenApp\Mapper\Ldap\People;

/**
 * Cette classe permet de synchroniser les rôles des personnes dans les
 * organisation.
 *
 * Class ConnectorPersonOrganization
 * @package Oscar\Connector
 */
class ConnectorPersonOrganization extends AbstractConnectorPersonOrganization
{
    /**
     * Service UnicaenApp pour obtenir les affectations.
     *
     * @var People
     */
    private $ldapPeople;

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
    public function __construct(
        $personConnector,
        $organizationConnector,
        PersonRepository $personRepository,
        OrganizationRepository $organizationRepository,
        People $ldapPeople,
        array $rolesConnection
    ) {
        parent::__construct($personConnector, $organizationConnector, $personRepository, $organizationRepository, $rolesConnection);
        $this->ldapPeople = $ldapPeople;
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



}