<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 19/11/15 10:52
 * @copyright Certic (c) 2015
 */

namespace Oscar\Controller;


use Doctrine\ORM\Query\Expr\Join;
use Oscar\Entity\Activity;
use Oscar\Entity\ActivityType;
use Oscar\Form\ActivityTypeForm;
use Zend\View\Model\ViewModel;

class ActivityTypeController extends AbstractOscarController
{
    public function indexAction()
    {
        return [
            'distribution' => $this->getActivityTypeService()->distribution(),
            'entities' => $this->getActivityTypeService()->getActivityTypes()
        ];
    }

    public function mergeAction()
    {
        /** @var ActivityType $destination */
        $destination = $this->getActivityTypeService()->getActivityType($this->params()->fromRoute('id'));
        if( !$destination ){
            die("Error, destination introuvable");
        }

        //$test = $this->getActivityTypeService()->getActivityTypeByCentaureId('ANR');

        $merged = explode(',', $this->params()->fromQuery('merged'));

        if( !$merged ){
            die("Rien à déplacer");
        }

        $mergedEntities = $this->getEntityManager()->getRepository(ActivityType::class)
            ->createQueryBuilder('t')
            ->where('t.id IN (:merged)')
            ->setParameter('merged', $merged)
            ->getQuery()
            ->getResult();



        // Récupération des activités à fusionner
        $activities = $this->getEntityManager()->getRepository(Activity::class)
            ->createQueryBuilder('a')
            ->where('a.activityType IN (:types)')
            ->setParameter('types', $merged)
            ->getQuery()
            ->getResult();

        /** @var Activity $activity */
        foreach( $activities as $activity ){
            echo "Mise à jour du type pour $activity\n<br>";
            $activity->setActivityType($destination);
        }
        $this->getEntityManager()->flush();

        // Suppression des types et update du code
        $newCode = $destination->getCentaureId();

        /** @var ActivityType $type */
        foreach( $mergedEntities as $type ){
            $this->getEntityManager()->refresh($type);
            echo "Suppression de  $type\n<br>";

            // Ajout du code centaure à la destination
            if( $newCode == '' ){
                $newCode = $type->getCentaureId();
            } else {
                if( $type->getCentaureId() ) {
                    $newCode .= '|' . $type->getCentaureId();
                }
            }
            $this->getActivityTypeService()->deleteNode($type);
        }
        $destination->setCentaureId($newCode);
        $this->getEntityManager()->flush($destination);

        $this->redirect()->toRoute('activitytype');
    }

    /**
     * Nouveau type d'activité.
     *
     * @return ViewModel
     */
    public function newAction()
    {
        $form = new ActivityTypeForm();
        $activityType = new ActivityType();
        $request = $this->getRequest();

        $parentId = intval($this->params()->fromRoute('idparent', 0));
        if( $parentId > 0 ){
            $parent = $this->getActivityTypeService()->getActivityType($parentId);
        } else {
            $parent = null;
        }

        // Traitement des données envoyées
        if( $request->isPost() ){
            $label = $this->params()->fromPost('label');
            $description = $this->params()->fromPost('description');
            $nature = $this->params()->fromPost('nature');

            $this->getEntityManager()->persist($activityType);
            $activityType->setDescription($description)
                ->setLabel($label)
                ->setNature($nature);

            $this->getActivityTypeService()->insertIn($activityType, $parent);
            $this->redirect()->toRoute('activitytype');
        }
        $view = new ViewModel([
            'entity' => $activityType,
            'form' => $form,
            'parent' => $parent,
        ]);
        $view->setTemplate('oscar/activity-type/form.phtml');
        return $view;
    }

    public function editAction()
    {
        $activityType = $this->getCurrentActivityType();
        $form = new ActivityTypeForm();
        $form->bind($activityType);

        if( $this->getRequest()->isPost() ){
            echo "POSTED !";
            $form->setData($this->getRequest()->getPost());
            if( $form->isValid() ) {
                echo "save !";
                $this->getEntityManager()->flush($form->getObject());
            } else {
                var_dump($form);
            }
        }
        $view = new ViewModel([
            'entity' => $activityType,
            'form' => $form,
            'parent' => null,
        ]);
        $view->setTemplate('oscar/activity-type/form.phtml');
        return $view;
    }

    public function moveAction()
    {
        $activityService = $this->getActivityTypeService();
        $nodeMoved = $activityService->getActivityType($this->params()->fromRoute('what'));
        $nodeDestination = $activityService->getActivityType($this->params()->fromRoute('where'));
        $movement = $this->params()->fromRoute('how');
        switch($movement){
            case 'in':
                $activityService->moveIn($nodeMoved, $nodeDestination);
                break;
            case 'after':
                $activityService->moveAfter($nodeMoved, $nodeDestination);
                break;
            case 'before':
                $activityService->moveBefore($nodeMoved, $nodeDestination);
                break;
            default:
                return $this->getResponseBadRequest('Unknow movement');
        }

        $this->redirect()->toRoute('activitytype');
    }

    public function deleteAction()
    {
        $activity = $this->getCurrentActivityType();
        $this->getActivityTypeService()->deleteNode($activity);
        $this->redirect()->toRoute('activitytype');

    }

    /**
     * @return ActivityType
     */
    private function getCurrentActivityType()
    {
        $typeId = $this->params()->fromRoute('id');
        return $this->getActivityTypeService()->getActivityType($typeId);
    }

}