<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/10/15 15:23
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;

use Oscar\Entity\LogActivity;
use Oscar\Entity\ProjectMember;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;

class MemberController extends AbstractOscarController
{
    /**
     * Affiche l'écran de gestion des membres.
     *
     * @return ViewModel
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function indexAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $project = $this->getEntityManager()
            ->getRepository('Oscar\Entity\Project')
            ->getSingle($id, ['ignoreDateMember' => true]);

        $view = new JsonModel($project->toArray()['members']);

        return $view;
    }

    /**
     * Supprime un membre d'un projet.
     *
     * @return Response|JsonModel
     */
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('memberid', 0);
        $projectMember = $this->getEntityManager()->getRepository('Oscar\Entity\ProjectMember')->find($id);
        if (!$projectMember || !$projectMember->getProject()) {
            return $this->getResponseBadRequest();
        }
        $project = $projectMember->getProject();
        $this->getEntityManager()->remove($projectMember);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->refresh($project);
        $this->getServiceLocator()->get('search')->update($project);

        $this->getActivityLogService()->addUserInfo(
            sprintf("a supprimé la personne %s (%s) du projet %s", $projectMember->getPerson()->log(), $projectMember->getRole(), $project->log()),
            $this->getDefaultContext(),
            $project->getId(),
            LogActivity::LEVEL_INCHARGE);

        return new JsonModel($project->toArray());
    }

    public function manageAction()
    {
        $personId = intval($this->params()->fromPost('enrolid'));
        $projectId = intval($this->params()->fromPost('ownerid'));
        $from = $this->params()->fromPost('dateStart', null);
        $to = $this->params()->fromPost('dateEnd', null);
        $role = strval($this->params()->fromPost('role'));

        // Transformation en date si possible
        $from = $from ? new \DateTime($from) : null;
        $to = $to ? new \DateTime($to) : null;

        $response = new Response();

        //// Test des données reçues
        if (!$personId || !$projectId || !$role) {
            $response->setStatusCode(501)
                ->setContent(_("Mauvaise utilisation de l'API"));

            return $response;
        } // Récupération des informations dans la BDD
        else {
            if (!($person = $this->getEntityManager()->getRepository('Oscar\Entity\Person')->find($personId))) {
                $response->setStatusCode(400)
                    ->setContent(sprintf(_("Personne '%s' inconnue."),
                        $personId));

                return $response;
            }
            /** @var \Oscar\Entity\Project $project */
            if (!($project = $this->getEntityManager()->getRepository('Oscar\Entity\Project')->find($projectId))) {
                $response->setStatusCode(400)
                    ->setContent(sprintf(_("Projet '%s' inconnu."),
                        $projectId));

                return $response;
            }
            if (!in_array($role, $this->getOscarUserContext()->getRolesPerson())) {
                $response->setStatusCode(400)
                    ->setContent(sprintf(_("Rôle '%s' inconnu."), $role));

                return $response;
            }
        }

        // Données
        $projectMember = new ProjectMember();
        $projectMember->setProject($project)
            ->setDateStart($from)
            ->setDateEnd($to)
            ->setPerson($person)
            ->setRole($role);

        // Récupération de la liste des rôles sur ce projet pour cette personne
        $rolesPerson = $project->getRolesPersonne($person, $role);

        foreach ($rolesPerson as $rolePerson) {
            if ($projectMember->intersect($rolePerson)) {
                $projectMember->extend($rolePerson);
                $this->getEntityManager()->remove($rolePerson);
            }
        }

        // Enregistrement
        try {
            $this->getEntityManager()->persist($projectMember);
            $this->getEntityManager()->flush();
            $this->getEntityManager()->refresh($project);
            $this->getServiceLocator()->get('search')->update($project);


            $this->getActivityLogService()->addUserInfo(
                sprintf("a ajouté la personne %s (%s) du projet %s", $projectMember->getPerson()->log(), $projectMember->getRole(), $project->log()),
                $this->getDefaultContext(),
                $project->getId(),
                LogActivity::LEVEL_INCHARGE);

            $response = new JsonModel($this->getEntityManager()->getRepository('Oscar\Entity\Project')->getSingle($projectId)->toArray());

            return $response;
        } catch (\Exception $e) {
            $response->setStatusCode(500)->setContent($e->getMessage());
        }

        return $response;
    }
}
