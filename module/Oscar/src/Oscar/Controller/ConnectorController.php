<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-02-01 15:14
 * @copyright Certic (c) 2017
 */

namespace Oscar\Controller;


use Oscar\Entity\Person;
use Oscar\Exception\OscarException;
use Oscar\Provider\Privileges;
use UnicaenCode\Controller\Controller;
use Zend\Http\Request;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;

class ConnectorController extends AbstractOscarController
{
    public function personAction()
    {

        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_CONNECTOR_ACCESS);
        /** @var Request $request */
        $request = $this->getRequest();

        $connectorName = $request->getQuery('c');
        $personId = $request->getQuery('v');

        $connectorsAvailabled = array_keys($this->getConfiguration('oscar.connectors.person'));
        if( ! in_array($connectorName, $connectorsAvailabled) ){
            return $this->getResponseBadRequest('Connecteur indisponible');
        } else {
            /** @var Person $person */
            $person = $this->getEntityManager()->getRepository(Person::class)->getPersonsByConnectorID($connectorName, $personId);
            if( count($person) == 1 ){
                $person = $person[0];
                $class = $this->getConfiguration('oscar.connectors.person')[$connectorName]['class'];
                $connector = $this->getServiceLocator()->get('ConnectorService')->getConnector('person.' . $connectorName);
                $connector->syncPerson($person);
                $this->getEntityManager()->flush();
                return $this->redirect()->toRoute('person/show', ['id' => $person->getId()]);
            } else {
                $match = [];
                foreach($person as $p){
                    $match[] = (string)$p;
                }
                throw new OscarException("Impossible de charger la personne.");
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
        return $this->getResponseNotImplemented('Synchronisation des organizations non implantée');
    }

    public function organizationsAction()
    {
        return $this->getResponseNotImplemented('Synchronisation des organizations non implantée');
    }

}