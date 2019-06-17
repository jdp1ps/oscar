<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-01-09 14:46
 * @copyright Certic (c) 2017
 */

namespace Oscar\Controller;

use Oscar\Entity\Activity;
use Oscar\Provider\Privileges;
use Oscar\Service\SpentService;
use Zend\Http\Request;
use Zend\View\Model\JsonModel;

class DepenseController extends AbstractOscarController
{
    /**
     * @return SpentService
     */
    public function getSpentService(){
        return $this->getServiceLocator()->get('SpentService');
    }

    public function manageSpendTypeGroupAction()
    {

        $this->getOscarUserContext()->check(Privileges::MAINTENANCE_SPENDTYPEGROUP_MANAGE);
        $format = $this->params()->fromQuery('format', 'html');


        $method = $this->getHttpXMethod();

        $this->getLogger()->debug("SPENTTYPE - $method");

        switch ($method) {
            case Request::METHOD_PUT:
                try {
                    $this->getLogger()->debug(print_r($_POST, true));
                    $result = $this->getSpentService()->createSpentTypeGroup($this->params()->fromPost());
                    return $this->getResponseOk();
                } catch (\Exception $e) {
                    $this->getLogger()->error($e->getMessage());
                    return $this->getResponseInternalError($e->getMessage());
                }

            case Request::METHOD_POST:
                try {
                    $this->getLogger()->debug(print_r($_POST, true));
                    if( $this->params()->fromPost("moved") ){
                        $result = $this->getSpentService()->moved($this->params()->fromPost('moved'), $this->params()->fromPost('to'));
                    }
                    elseif ($this->params()->fromPost("admin")) {
                        $this->getSpentService()->admin($this->params()->fromPost('admin'));
                    }
                    elseif ($this->params()->fromPost("action") == 'blind') {
                        $spent = $this->getSpentService()->getSpentTypeById($this->params()->fromPost('id'));
                        $spent->setBlind(!$spent->getBlind());
                        $this->getEntityManager()->flush($spent);
                        return $this->getResponseOk();
                    }

                    else {
                        $result = $this->getSpentService()->updateSpentTypeGroup($this->params()->fromPost());
                    }
                    return $this->jsonOutput([
                        'spenttypegroups' => $this->getSpentService()->getAllArray()
                    ]);
                } catch (\Exception $e) {
                    $this->getLogger()->error($e->getMessage());
                    return $this->getResponseInternalError($e->getMessage());
                }

            case Request::METHOD_DELETE:
                try {
                    $this->getLogger()->debug(print_r($_GET, true));
                    $deleteId = $this->params()->fromQuery('id');
                    $result = $this->getSpentService()->deleteNode(
                        $this->getSpentService()->getSpentGroupNodeData($deleteId));
                    return $this->jsonOutput([
                        'spenttypegroups' => $this->getSpentService()->getAllArray()
                    ]);
                } catch (\Exception $e) {
                    $this->getLogger()->error($e->getMessage());
                    return $this->getResponseInternalError($e->getMessage());
                }


            case Request::METHOD_GET:
                break;

            default:
                return $this->getResponseBadRequest();
        }


        if( $format == 'json' || $this->isAjax() ){
            return $this->jsonOutput([
                'spenttypegroups' => $this->getSpentService()->getAllArray()
            ]);
        } else {
            return [];
        }
        die("TODO");
    }


    /**
     * Retourne la liste des dépenses pour l'activité
     *
    public function activityAction()
    {
        $idActivity = $this->params()->fromRoute('idactivity', null);
        $page = $this->params()->fromQuery('page', 1);

        // Appel avec une idActivity => appel ajax depuis la fiche détaillée de
        // l'activité.
        if( $idActivity ) {
            $activity = $this->getEntityManager()->getRepository(Activity::class)->find($idActivity);
            $this->getOscarUserContext()->check(Privileges::MAINTENANCE_MENU_ADMIN, $activity);

            ////////////////////////////////////////////////////////////////////
            $entities = [
                [
                    'id' => 1,
                    'label' => "Exemple 1 de depense",
                    'description' => "Description de la dépense 1",
                    'date' => '2016-10-08',
                    'numero' => 'TESTDEPENSE001',
                    'yearref' => '2016',
                    'type' => 'functionnal',
                    'amount' =>  [
                        'value' =>589.9,
                        'currency' => '€',
                        'rate' => 1.0
                    ]
                ],
                [
                    'id' => 2,
                    'label' => "Exemple 2 de depense",
                    'description' => "Description de la dépense 2",
                    'date' => '2015-05-22',
                    'numero' => 'TESTDEPENSE002',
                    'yearref' => '2015',
                    'type' => 'salaire',
                    'amount' =>  [
                        'value' =>2645.3,
                        'currency' => '€',
                        'rate' => 1.0
                    ]
                ]
            ];


//            $qb = $this->getEntityManager()->getRepository(ActivityPayment::class)->createQueryBuilder('p')
//                ->addSelect('c')
//                ->innerJoin('p.activity', 'a')
//                ->innerJoin('p.currency', 'c')
//                ->where('a.id = :idactivity')
//                ->orderBy('p.status', 'DESC')
//                ->addOrderBy('p.datePayment');
//            $entities = $qb->setParameter('idactivity', $idActivity)->getQuery()->getResult(Query::HYDRATE_ARRAY);

            $view = new JsonModel($entities);
            return $view;
        }

        return $this->getResponseBadRequest("Appel non conforme");

    }
    /******/

}