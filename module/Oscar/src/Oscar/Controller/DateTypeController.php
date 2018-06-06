<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 27/01/16 14:38
 * @copyright Certic (c) 2016
 */

namespace Oscar\Controller;


use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\DateType;
use Oscar\Entity\DateTypeRepository;
use Oscar\Exception\OscarException;
use Oscar\Form\DateTypeForm;
use Zend\View\Model\ViewModel;

class DateTypeController extends AbstractOscarController
{
    public function indexAction()
    {
        return [
            'entities' => $this->getEntityManager()->getRepository(DateType::class)->allWithUsage()
        ];
    }

    public function newAction()
    {
        $form = new DateTypeForm();
        $request = $this->getRequest();
        $entity = new DateType();
        $form->setObject($entity);
        $form->setAttribute('action', $this->url()->fromRoute('datetype/new'));

        // Traitement des données envoyées
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getEntityManager()->persist($entity);
                $this->getEntityManager()->flush();
                $this->redirect()->toRoute('datetype');
            }
        }

        $view = new ViewModel([
            'entity' => $entity,
            'form' => $form,
        ]);

        $view->setTemplate('oscar/date-type/form.phtml');
        return $view;
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        $dateType = $this->getEntityManager()->getRepository(DateType::class)->find($id);

        $jalons = $this->getEntityManager()->getRepository(ActivityDate::class)->findBy([
            'type' => $dateType
        ]);

        if( count($jalons) > 0 ){
            throw new OscarException("Impossible de supprimer un type de jalon utilisé dans les activités");
        } else {
            try {
                $this->getEntityManager()->remove($dateType);
                $this->getEntityManager()->flush();
                return $this->redirect()->toRoute('datetype');
            } catch ( ForeignKeyConstraintViolationException $e ){
                throw new OscarException("Oscar n'a pas pu supprimer le type de jalon : " . $e->getMessage());
            }
        }
    }

    public function editAction()
    {
        $form = new DateTypeForm();
        $entity = $this->getEntityManager()->getRepository(DateType::class)->find($this->params()->fromRoute('id'));
        $request = $this->getRequest();
        $form->setAttribute('action', $this->url()->fromRoute('datetype/edit', ['id' => $entity->getId()]));
        //$form->init();
        $form->bind($entity);
        $form->get('finishable')->setAttribute('checked', ($form->get('finishable')->getValue()) ? 'checked' : '');

        // Traitement des données envoyées
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getEntityManager()->persist($entity);
                $this->getEntityManager()->flush();
                $this->redirect()->toRoute('datetype');
            }
        }

        $view = new ViewModel([
            'entity' => $entity,
            'form' => $form,
        ]);

        $view->setTemplate('oscar/date-type/form.phtml');
        return $view;
    }

}