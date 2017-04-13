<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 10/09/15 15:10
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

use Oscar\Entity\LogActivity;
use Oscar\Entity\ProjectPartner;
use Zend\View\Model\JsonModel;

class PartnerController extends AbstractOscarController
{
    public function indexAction()
    {
        $projectId = $this->params()->fromRoute('id', 0);

        $projectRepository = $this->getProjectRepository();
        /** @var \Oscar\Entity\Project $project */
        $project = $projectRepository->getById($projectId);

        return new JsonModel($project->toArray()['partners']);
    }

    public function showAction($id)
    {
        die('Not Implemented');
    }

    public function deleteAction($id = 'none')
    {
        if ($this->getRequest()->isDelete()) {
            $id = (int) $this->params()->fromRoute('partnerid');
            try {
                $partner = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectPartner')->find($id);
                $this->getEntityManager()->remove($partner);
                $this->getEntityManager()->flush();
                $this->getActivityLogService()->addUserInfo(
                    sprintf('a supprimé le partenaire %s (%s) du projet %s', $partner->getOrganization()->log(), $partner->getRole(), $partner->getProject()->log()),
                    $this->getDefaultContext(),
                    $partner->getProject()->getId(),
                    LogActivity::LEVEL_INCHARGE);
                die();
            } catch (\Exception $e) {
                $this->getResponse()->setStatusCode(500);
                die($e->getMessage());
            }
        }
        $this->getResponse()->setStatusCode(500);
        die('Bad usage');
    }

    public function manageAction()
    {
        if ($this->getRequest()->isPost()) {
            $dateStart = $this->params()->fromPost('dateStart');
            $dateEnd = $this->params()->fromPost('dateEnd');
            $role = $this->params()->fromPost('role');
            $enrolId = $this->params()->fromPost('enrolid');
            $projectId = $this->params()->fromPost('ownerid');

            $project = $this->getEntityManager()->getRepository('Oscar\Entity\Project')->find($projectId);
            $organisation = $this->getEntityManager()->getRepository('Oscar\Entity\Organization')->find($enrolId);
            $partner = new ProjectPartner();
            $partner->setProject($project)
                ->setOrganization($organisation)
                ->setDateStart($dateStart ? new \DateTime($dateStart) : null)
                ->setDateEnd($dateEnd ? new \DateTime($dateEnd) : null)
                ->setRole($role);
            $this->getEntityManager()->persist($partner);
            $this->getEntityManager()->flush();

            $this->getActivityLogService()->addUserInfo(
                sprintf('a ajouté le partenaire %s (%s) au projet %s', $organisation->log(), $role, $project->log()),
                $this->getDefaultContext(),
                $project->getId(),
                LogActivity::LEVEL_INCHARGE);



            die();
        }

        $this->getResponse()->setStatusCode(500);
        die('Bad usage');
    }
}
