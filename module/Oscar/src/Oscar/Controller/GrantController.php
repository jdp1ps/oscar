<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 01/06/15 13:34
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\Controller;

use Oscar\Entity\GrantSource;
use Oscar\Form\GrantSourceForm;
use Zend\View\Model\ViewModel;

/**
 * Permet de gérer les sources de financement.
 */
class GrantController extends AbstractOscarController
{
    public function indexAction()
    {
        $year = $this->params()->fromQuery('year', date('Y'));

        return array(
            'year' => $year,
            'contracts' => $this->getGrantRepository()->getAllByYear($year),
        );
    }

    public function sourceListAction()
    {
        return array(
            'entities' => $this->getListGrant(),
        );
    }

    /**
     * Nouveau.
     *
     * @return array
     */
    public function newAction()
    {
        $form = new GrantSourceForm();
        $request = $this->getRequest();
        $entity = new GrantSource();

        if ($request->isPost()) {
            // datas validation
            $datas = $request->getPost();
            $form->setData($datas);

            $em = $this->getEntityManager();
            $new = new GrantSource();
            $new->setLabel($datas['label']);
            $new->setDescription($datas['description']);
            $new->setInformations($datas['informations']);
            $em->persist($new);
            $em->flush();
            $this->flashMessenger()->addSuccessMessage(sprintf('La source de financement %s a bien été ajouté', $new->getLabel()));

            $this->redirect()->toRoute('grantsource_index'); //, array('id' => $new->getId()));
        }

        return array(
            'form' => $form,
            'entity' => $entity,
        );
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $em = $this->getEntityManager();

        $entity = $em->find('\Oscar\Entity\GrantSource', $id);

        $form = new GrantSourceForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            // datas validation
            $datas = $request->getPost();
            $form->setData($datas);
            $entity->setLabel($datas['label']);
            $entity->setDescription($datas['description']);
            $entity->setInformations($datas['informations']);
            $em->flush();
            $this->flashMessenger()->addSuccessMessage(sprintf('La source de financement %s a bien été mise à jour', $entity->getLabel()));

            return $this->redirect()->toRoute('grantsource_index');
        } else {
            $form->setData($entity->asArray());
        }

        $view = new ViewModel(array(
            'form' => $form,
            'entity' => $entity,
        ));

        $view->setTemplate('oscar/grant/new');

        return $view;
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $em = $this->getEntityManager();

        $entity = $em->find('\Oscar\Entity\GrantSource', $id); /* @var GrantSource **/
        $em->remove($entity);
        $this->flashMessenger()->addSuccessMessage(sprintf('La source de financement %s a bien été supprimée', $entity->getLabel()));
        $em->flush();

        return $this->redirect()->toRoute('grantsource_index');
    }

    // Liste des fincancements en base de données
    protected function getListGrant()
    {
        return $this->getEntityManager()->getRepository('Oscar\Entity\GrantSource')->findAll();
    }
}
