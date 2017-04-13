<?php

namespace Oscar\Controller;

use Oscar\Entity\ProjectPartner;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;

/**
 * @author  Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 */
class ApiController extends AbstractOscarController
{
    public function apiAction()
    {
        $action = $this->params()->fromQuery('a');
        $result = [];

        if (!$action) {
            $this->response->setStatusCode(500);
            $result['error'] = 'Mauvais utilisation';
        } else {
            try {
                $projectId = $this->params()->fromQuery('projectId');
                switch ($action) {
                    case 'partners':

                        if ($this->getRequest()->isPost()) {
                            $dateStart = $this->params()->fromPost('dateStart');
                            $dateEnd = $this->params()->fromPost('dateEnd');
                            $role = $this->params()->fromPost('role');
                            $enrolId = $this->params()->fromPost('enrolid');

                            $this->addPartner($projectId, $enrolId, $role, $dateStart, $dateEnd);
                        } elseif ($this->getRequest()->isDelete()) {
                            var_dump($this->getRequest()->getParameters());
                            $projectPartner = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectPartner')->find($this->param('idRole'));
                            $this->getEntityManager()->remove($projectPartner);
//                            throw new \Exception("SUPPRESSION");
                        }

                        $result = $this->getProject($projectId)['partners'];

                        break;

                    case 'members':
                        $result = $this->getProject($projectId)['members'];
                        break;

                    case 'identification':
                        $result = $this->getProject($projectId);
                        break;

                    case 'delete-partner':
                        $result = $this->deleteRole();
                        break;

                    default:
                        throw new \Exception('Unknow API action');
                        break;
                }
            } catch (\Exception $ex) {
                $this->response->setStatusCode(500);
                $result['error'] = $ex->getMessage();
            }
        }

        return new JsonModel($result);
    }

    protected function addPartner($projectId, $enrolId, $role, $dateStart, $dateEnd)
    {
        $project = $this->getEntityManager()->getRepository('Oscar\Entity\Project')->find($projectId);
        $organisation = $this->getEntityManager()->getRepository('Oscar\Entity\Organization')->find($enrolId);
        $partner = new ProjectPartner();
        $partner->setProject($project)
            ->setOrganization($organisation)
            ->setDateStart(new \DateTime($dateStart))
            ->setDateEnd(new \DateTime($dateEnd))
            ->setRole($role);
        $this->getEntityManager()->persist($partner);
        $this->getEntityManager()->flush();
    }

    protected function deleteRole()
    {
        $projectId = $this->params()->fromQuery('projectId');

        return ['response' => 'TOTO '.$projectId];
    }

    protected function getProject($projectId)
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\Project')->getSingle($projectId)->toArray();
    }

    protected function getProjectMembers($projectId)
    {
        return $this->getProject($projectId)['members'];
    }

    protected function getProjectPartners($projectId)
    {
        return $this->getProject($projectId)['partners'];
    }

    /**
     * @return ViewModel
     */
    public function searchStaffAction()
    {
        $sl = $this->getServiceLocator();
        $search = $this->getRequest()->getQuery()->get('q');
        $this->getLogger()->debug($search);

        if (strlen($search) >= 4) {
            $t = $sl->get('PersonnelService');
            /* @var \Application\Service\PersonnelService */
            $result = $t->searchStaff($search);
            $this->getLogger()->debug(print_r($result));

            return new JsonModel($result);
        } else {
            $sl->get('logger')->addError(get_class().'::searchStaffAction (too short search)');
            $response = new Response();
            $response->setStatusCode(400);

            return $response;
        }
    }
}
