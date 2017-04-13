<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 27/01/16 14:38
 * @copyright Certic (c) 2016
 */

namespace Oscar\Controller;


use Oscar\Entity\DateType;
use Oscar\Form\DateTypeForm;
use Zend\View\Model\ViewModel;

class DateTypeController extends AbstractOscarController
{
    public function indexAction(){
        return [
            'entities' => $this->getEntityManager()->getRepository(DateType::class)->findAll()
        ];
    }

    public function newAction(){
        $form = new DateTypeForm();
        //$entity = $this->getEntityManager()->getRepository(DateType::class)->find($this->params()->fromRoute('id'));
        $request = $this->getRequest();
        $entity = new DateType();
        $form->setObject($entity);
        $form->setAttribute('action', $this->url()->fromRoute('datetype/new'));



        // Traitement des données envoyées
        if( $request->isPost() ){
            $form->setData($request->getPost());
            if( $form->isValid() ){
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

    public function deleteAction(){
        die('TODO');
    }

    public function editAction(){
        die('TODO');
    }

}