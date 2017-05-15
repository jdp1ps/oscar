<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Connector\ConnectorPersonOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\ProjectMember;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\Mapper\Ldap\People;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Gestion des Personnes :
 *  - Collaborateurs
 *  - Membres de projet/organisation.
 */
class ConnectorService implements ServiceLocatorAwareInterface, EntityManagerAwareInterface
{
    use ServiceLocatorAwareTrait, EntityManagerAwareTrait;


    private $_CONNECTORS_INSTANCE = [];

    public function getConnector( $connectorName ){

        // Création de l'instance
        if( !array_key_exists($connectorName, $this->_CONNECTORS_INSTANCE) ){
            /** @var ConfigurationParser $oscarConfig */
            $oscarConfig = $this->getServiceLocator()->get('OscarConfig');

            // Configuration du connecteur
            $connectorConfig = $oscarConfig->getConfiguration('connectors.'.$connectorName);

            // Création de l'instance
            $_CONNECTORS_INSTANCE[$connectorName] = new $connectorConfig['class'];
            $_CONNECTORS_INSTANCE[$connectorName]->init($this->getServiceLocator(), $connectorConfig['params']);
        }
        return $_CONNECTORS_INSTANCE[$connectorName];
    }
}
