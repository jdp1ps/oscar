<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 14/09/15 10:32
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

use Oscar\Entity\Project;
use Oscar\Entity\Activity;
use Oscar\Utils\UnicaenDoctrinePaginator;
use Laminas\Http\Request;

/**
 * Class ContractController
 * @package Oscar\Controller
 * @deprecated
 */
class ContractController extends AbstractOscarController
{
    ////////////////////////////////////////////////////////////////////////////
    //
    ////////////////////////////////////////////////////////////////////////////
    public function manageGrantAction()
    {
        $method = $this->getRequest()->getMethod();

        switch ($method) {
            case Request::METHOD_DELETE:
                try {
                    $grant = $this->getProjectGrantFromRoute();
                    die("ERREUR manageGrantAction");
                    $grant->setStatus(Activity::STATUS_CLOSED);
                    $this->getEntityManager()->flush($grant);

                    return $this->getResponseOk();
                } catch (\Exception $e) {
                    return $this->getResponseInternalError('Impossible de supprimer le contrat');
                }
                break;
            default:
                return $this->getResponseNotImplemented();
        }
    }

    /**
     * @return Project
     */
    protected function getProjectFromRoute()
    {
        $projectId = $this->params()->fromRoute('idproject');
        return $this->getEntityManager()->getRepository('Oscar\Entity\Project')->find($projectId);
    }

    /**
     * @return Activity
     */
    protected function getProjectGrantFromRoute()
    {
        $idGrant = $this->params()->fromRoute('idgrant');
        return $this->getEntityManager()->getRepository('Oscar\Entity\ProjectGrant')->find($idGrant);
    }

    public function showAction()
    {
        $idGrant = $this->params()->fromRoute('idgrant');
        die('showAction'.$idGrant);
    }

    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->orderBy('c.dateCreated', 'DESC')
        ;
        $qb->select('c')->from(Activity::class, 'c');
        return [
            'contracts' => new UnicaenDoctrinePaginator($qb, $page)
        ];
    }

//    {
//        $idProject = $this->params()->fromRoute('idproject');
//        /** @var \Oscar\Entity\ProjectRepository $repository */
//        $repository = $this->getEntityManager()->getRepository('Oscar\Entity\Project');
//        $data = [];
//        $data['project'] = $project = $repository->getSingle($idProject);
//        if (!$project) {
//            throw new \HttpException("Le projet %s n'existe pas");
//        }
//
//        if ($this->request->isPost()) {
//            try {
//                $this->getLogger()->addInfo(print_r($this->params()->fromPost('model'), true));
//                $datas = json_decode($this->params()->fromPost('model'));
//                $grant = new Activity();
//                $this->getEntityManager()->persist($grant);
//                $grant->setAmount($datas->amount);
//                $grant->setProject($project);
//
//                $type = $this->getEntityManager()->getRepository('Oscar\Entity\ContractType')->find($datas->idtype);
//                $source = $this->getEntityManager()->getRepository('Oscar\Entity\GrantSource')->find($datas->idsource);
//                $grant->setType($type)
//                    ->setSource($source);
//
//                $this->getEntityManager()->flush($grant);
//                $view = new JsonModel();
//                $view->setVariables($grant->toArray());
//
//                return $view;
//            } catch (\Exception $e) {
//                $response = new Response();
//                $response->setStatusCode(Response::STATUS_CODE_500);
//                $response->setContent('Not implemented');
//
//                return $response;
//            }
//        }
//
//        $data['types'] = $this->getEntityManager()->getRepository('Oscar\entity\ContractType')->findAll();
//        $data['sources'] = $this->getEntityManager()->getRepository('Oscar\entity\GrantSource')->findAll();
//
//        if ($this->getRequest()->isXmlHttpRequest()/**/) {
//            $view = new JsonModel();
//            $data = $project->toArray()['grants'];
//        } else {
//            $view = new ViewModel();
//            $data['result'] = $project;
//        }
//
//        $view->setVariables($data);
//
//        return $view;
//    }
}
