<?php

namespace Oscar\Service;

use Doctrine\ORM\Query;
use Oscar\Connector\ConnectorPersonOrganization;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationPerson;
use Oscar\Entity\Person;
use Oscar\Entity\PersonRepository;
use Oscar\Entity\ProjectMember;
use Oscar\Traits\UseOscarConfigurationService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Utils\UnicaenDoctrinePaginator;
use UnicaenApp\Mapper\Ldap\People;
use UnicaenApp\Service\EntityManagerAwareInterface;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use UnicaenApp\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Gestion des Personnes :
 *  - Collaborateurs
 *  - Membres de projet/organisation.
 */
class ConnectorService implements UseOscarConfigurationService
{
    use UseOscarConfigurationServiceTrait;


    private $_CONNECTORS_INSTANCE = [];

    public function getConnector( $connectorName ){

        // Création de l'instance
        if( !array_key_exists($connectorName, $this->_CONNECTORS_INSTANCE) ){

            /** @var ConfigurationParser $oscarConfig */
            $oscarConfig = $this->getOscarConfigurationService();

            // Configuration du connecteur
            $connectorConfig = $oscarConfig->getConfiguration('connectors.'.$connectorName);

            // Slip pour garder le nom "simple"
            $spliName = explode('.', $connectorName);
            $connectorShortName = $spliName[count($spliName)-1];

            // Création de l'instance
            $_CONNECTORS_INSTANCE[$connectorName] = new $connectorConfig['class'];
            $_CONNECTORS_INSTANCE[$connectorName]->init(
                $this->getServiceLocator(),
                $connectorConfig['params'],
                $connectorShortName);

            if( array_key_exists('editable', $connectorConfig) ){
                $_CONNECTORS_INSTANCE[$connectorName]->setEditable($connectorConfig['editable'] === true);
            }

            if( array_key_exists('hooks', $connectorConfig) ){
                $_CONNECTORS_INSTANCE[$connectorName]->setHooks($connectorConfig['hooks']);
            }
        }
        return $_CONNECTORS_INSTANCE[$connectorName];
    }
}
