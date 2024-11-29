<?php

namespace Oscar\Controller;

use DateTime;
use BjyAuthorize\Exception\UnAuthorizedException;
use Laminas\Http\Response;
use Laminas\View\Model\JsonModel;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityNote;
use Oscar\Provider\Privileges;
use Throwable;

class ActivityNotesController extends AbstractOscarController
{

    public function apiAction()
    {
        try {
            if ($this->getRequest()->getMethod() == "GET") {
                return $this->getNotes();
            }

            if ($this->getRequest()->getMethod() == "POST") {

                $activity_id = $this->getRequest()->getQuery('activityid');
                if (!$activity_id) {
                    throw new \Exception("activityid query param mandatory");
                }
        
                /** @var Activity $activity */
                $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activity_id);
                if (!$activity) {
                    throw new \Exception("activityid not found in DB");
                }

                if (!$this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_NOTES_MANAGE_ADMIN, $activity)
                    && !$this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_NOTES_MANAGE_USER, $activity)) {
                    throw new UnAuthorizedException('Droits insuffisants');
                }

                $action_note_json = json_decode($this->getRequest()->getContent());

                if ($action_note_json->action == "create") {
                    return $this->createNote($action_note_json, $activity);
                }
                if ($action_note_json->action == "update") {
                    return $this->updateNote($action_note_json, $activity);
                }
               
                if ($action_note_json->action == "delete") {
                    return $this->deleteNote($action_note_json->note_id, $activity);
                }
            }

            $response = new Response();
            $response->setStatusCode(Response::STATUS_CODE_405);
            $response->setContent("Method Not Allowed.");
            return $response;

        } catch (Throwable $e) {
            $response = new Response();
            $response->setStatusCode(Response::STATUS_CODE_500);
            $response->setContent($e->getMessage());
            return $response;
        }
    }

    private function getNotes() {
        $activity_id = $this->getRequest()->getQuery('activityid');
        if (!$activity_id) {
            throw new \Exception("activityid query param mandatory");
        }

        /** @var Activity $activity */
        $activity = $this->getEntityManager()->getRepository(Activity::class)->find($activity_id);
        if (!$activity) {
            throw new \Exception("activityid not found in DB");
        }

        $this->getOscarUserContextService()->check(Privileges::ACTIVITY_NOTES_SHOW, $activity);

        $allNotes = $this->getEntityManager()
                ->getRepository(ActivityNote::class)
                ->createQueryBuilder('activitynotes')
                ->select('activitynotes')
                ->where('activitynotes.activity = :activity_id')
                ->addOrderBy('COALESCE(activitynotes.dateUpdated, activitynotes.dateCreated)', 'DESC')
                ->setParameter('activity_id', $activity_id)
                ->getQuery()
                ->getResult();

        $json = [
            'notes' => []
        ];

        /** @var ActivityNote $note */
        foreach ($allNotes as $note) {
            $date_updated = $note->getDateCreated()->format(DateTime::ATOM);
            if ($note->getDateUpdated() != NULL) {
                $date_updated = $note->getDateUpdated()->format(DateTime::ATOM);
            }

            $json['notes'][] = [
                'id'          => $note->getId(),
                'content'     => $note->getContent(),
                'date_updated' => $date_updated,
                'created_by' => [
                    'id' => $note->getCreatedBy() ? $note->getCreatedBy()->getId() : -1,
                    'first_name'      => $note->getCreatedBy() ? $note->getCreatedBy()->getFirstName() : "",
                    'last_name'      => $note->getCreatedBy() ? $note->getCreatedBy()->getLastName() : "",
                ],
            ];
        }

        $response = new JsonModel();
        $response->setVariables($json);
        return $response;
    }

    private function createNote($action_note_json, $activity) {
        $note = new ActivityNote();
        $note->setContent($action_note_json->content);
        $note->setCreatedBy($this->getCurrentPerson());
        $note->setActivity($activity);
        $this->getEntityManager()->persist($note);
        $this->getEntityManager()->flush();

        $response = new JsonModel();
        $response->setVariables(['id' => $note->getId()]);
        return $response;
    }

    private function updateNote($action_note_json, $activity) {
        $note = $this->getEntityManager()
            ->getRepository(ActivityNote::class)
            ->findOneBy(array('id' => $action_note_json->note_id));
        if (!$note) {
            throw new \Exception("La note n'existe pas. Veuillez actualiser la page.");
        }

        if (!$this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_NOTES_MANAGE_ADMIN, $activity)
            && (!$this->getCurrentPerson()
                 || $this->getCurrentPerson()->getId() != $note->getCreatedBy()->getId())) {
            throw new UnAuthorizedException('Droits insuffisants');
        }

        $note->setContent($action_note_json->content);
        $note->setUpdatedBy($this->getCurrentPerson());
        $note->setDateUpdated(new \Datetime());

        $this->getEntityManager()->persist($note);
        $this->getEntityManager()->flush();

        $response = new JsonModel();
        $response->setVariables(['id' => $note->getId()]);
        return $response;
    }

    private function deleteNote($note_id, $activity) {
        $note = $this->getEntityManager()
            ->getRepository(ActivityNote::class)
            ->findOneBy(array('id' => $note_id));
        if (!$note) {
            throw new \Exception("La note n'existe pas. Veuillez actualiser la page.");
        }

        if (!$this->getOscarUserContextService()->hasPrivileges(Privileges::ACTIVITY_NOTES_MANAGE_ADMIN, $activity)
            && (!$this->getCurrentPerson()
                 || $this->getCurrentPerson()->getId() != $note->getCreatedBy()->getId())) {
            throw new UnAuthorizedException('Droits insuffisants');
        }

        $this->getEntityManager()->remove($note);
        $this->getEntityManager()->flush();

        $response = new JsonModel();
        $response->setVariables(['id' => $note->getId()]);
        return $response;
    }

}
