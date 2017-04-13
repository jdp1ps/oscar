<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-02-01 15:14
 * @copyright Certic (c) 2017
 */

namespace Oscar\Controller;


use Oscar\Entity\Person;
use UnicaenCode\Controller\Controller;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class ConnectorController extends AbstractOscarController
{
    public function personAction()
    {
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
                $connector = $this->getConfiguration('oscar.connectors.person')[$connectorName]();

                if( $connector instanceof ServiceLocatorAwareInterface ){
                    $connector->setServiceLocator($this->getServiceLocator());
                }
                $person = $connector->syncPerson($person);
                $this->getEntityManager()->flush();
                //var_dump($person);
                return $this->redirect()->toRoute('person/show', ['id' => $person->getId()]);
                //return $this->ajaxResponse($person->toArray());
            } else {
                $match = [];
                foreach($person as $p){
                    $match[] = (string)$p;
                }
                return $this->getResponseInternalError(implode(', ', $match) . ' ~ Données non-unique ou null');
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