<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-02-01 15:14
 * @copyright Certic (c) 2017
 */

namespace Oscar\Controller;


use Oscar\Connector\ConnectorOrganizationREST;
use Oscar\Entity\Organization;
use Oscar\Entity\OrganizationRepository;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use Oscar\Service\ConnectorService;
use Oscar\Traits\UseOscarConfigurationServiceTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UsePersonService;
use Oscar\Traits\UsePersonServiceTrait;
use Oscar\Traits\UseServiceContainer;
use Oscar\Traits\UseServiceContainerTrait;
use UnicaenCode\Controller\Controller;
use Laminas\Http\Request;


class ConnectorController extends AbstractOscarController implements UseOscarUserContextService, UsePersonService,
                                                                     UseServiceContainer
{
    use UseOscarConfigurationServiceTrait, UsePersonServiceTrait, UseServiceContainerTrait;

    public function personAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_CONNECTOR_ACCESS);
        /** @var Request $request */
        $request = $this->getRequest();

        $connectorName = $request->getQuery('c');
        $personId = $request->getQuery('v');

        $connectorsAvailabled = array_keys(
            $this->getOscarConfigurationService()->getConfiguration('connectors.person')
        );

        if (!in_array($connectorName, $connectorsAvailabled)) {
            return $this->getResponseBadRequest('Connecteur indisponible');
        } else {
            $personService = $this->getPersonService();
            $persons = $personService->getPersonRepository()->getPersonsByConnectorID($connectorName, $personId);

            if (count($persons) == 1) {
                $person = $persons[0];
                $class = $this->getOscarConfigurationService()->getConfiguration(
                    'connectors.person'
                )[$connectorName]['class'];
                $connector = $this->getServiceContainer()->get(ConnectorService::class)->getConnector(
                    'person.' . $connectorName
                );
                $connector->setOptionPurge(true);
                $connector->syncPerson($person);
                $this->getEntityManager()->flush();
                return $this->redirect()->toRoute('person/show', ['id' => $person->getId()]);
            } else {
                $match = [];
                foreach ($persons as $p) {
                    $match[] = (string)$p;
                }
                throw new OscarException(
                    "Plusieurs personne partage le connecteur ID '$personId' : " . implode(',', $match)
                );
            }
        }
        return $this->getResponseNotImplemented('Synchronisation des personnes non implantée');
    }

    public function personsAction()
    {
        return $this->getResponseNotImplemented('Synchronisation des personnes non implantée');
    }

    public function organizationAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_CONNECTOR_ACCESS);
        /** @var Request $request */
        $request = $this->getRequest();

        $connectorName = $request->getQuery('c');
        $organizationId = $request->getQuery('v');

        $connectorsAvailabled = array_keys(
            $this->getOscarConfigurationService()->getConfiguration('connectors.organization')
        );

        if (!in_array($connectorName, $connectorsAvailabled)) {
            return $this->getResponseBadRequest('Connecteur indisponible');
        } else {
            $personService = $this->getPersonService();

            // Je sais c'est moche
            /** @var OrganizationRepository $organizationRepository */
            $organizationRepository = $personService->getEntityManager()->getRepository(Organization::class);

            $organization = $organizationRepository->getObjectByConnectorID($connectorName, $organizationId);

            if ($organization) {
                $class = $this->getOscarConfigurationService()->getConfiguration(
                    'connectors.organization'
                )[$connectorName]['class'];

                /** @var ConnectorOrganizationREST $connector */
                $connector = $this->getServiceContainer()->get(ConnectorService::class)->getConnector(
                    'organization.' . $connectorName
                );
                $connector->syncOrganization($organization);
                $this->getEntityManager()->flush();
                return $this->redirect()->toRoute('organization/show', ['id' => $organization->getId()]);
            } else {
                throw new OscarException("Plusieurs organizations partagent le connecteur ID '$organizationId'");
            }
        }
    }

    public function organizationsAction()
    {
        return $this->getResponseNotImplemented('Synchronisation des organizations non implantée');
    }

}