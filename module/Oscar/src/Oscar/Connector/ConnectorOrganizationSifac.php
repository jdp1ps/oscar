<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-09-15 08:46
 * @copyright Certic (c) 2016
 */

namespace Oscar\Connector;


use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;

class ConnectorOrganizationSifac extends AbstractConnectorOracle implements IConnectorOrganization, IConnector
{
    /**
     * ConnectorPersonHarpege constructor.
     */
    public function __construct(array $params, array $fieldsConfiguration)
    {
        parent::__construct($params);
        $this->setQueryOne($this->getParam('queryOrganization'));
        $this->setQueryAll($this->getParam('queryOrganizations'));
        $this->configureFieldUpdate($this->getParam('dateUpdatedField'));
        $this->buildFieldConfiguration($fieldsConfiguration);
    }

    function getOrganizationData($idConnector)
    {
        return null;
    }

    function syncOrganizations(OrganizationRepository $repository, $force)
    {
        return $this->syncAll($repository, $force);
    }

    function syncOrganization(Organization $organization)
    {
        $id = $organization->getConnectorID($this->getName());
        return $this->syncOne($organization, $id);
    }

    function getName()
    {
        return 'sifac';
    }


    function getRemoteID()
    {
        return 'ID';
    }
}