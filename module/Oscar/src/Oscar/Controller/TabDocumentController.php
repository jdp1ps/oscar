<?php
/**
 * @author Hervé Marie<herve.marie@unicaen.fr>
 * @date: 20/10/22
 * @copyright Certic (c) 2022
 */

namespace Oscar\Controller;


use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Oscar\Entity\Role;
use Oscar\Entity\TabDocument;
use Oscar\Exception\OscarException;
use Oscar\Form\TabDocumentForm;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class TabDocumentController extends AbstractOscarController
{
    /**
     * Accueil Page gestion onglets de documents
     * @return array
     */
    public function indexAction():array
    {
        return [
            'entities' => $this->getEntityManager()->getRepository(TabDocument::class)->findAll()
        ];
    }

    /**
     * Ajout d'un nouvel onglet de document
     *
     * @return ViewModel
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function newAction():ViewModel
    {
        $roles = $this->getEntityManager()->getRepository(Role::class)->findAll();
        $form = new TabDocumentForm($roles, $this->getEntityManager());
        $request = $this->getRequest();
        $entity = new TabDocument();
        $form->setObject($entity);
        $form->setAttribute('action', $this->url()->fromRoute('tabdocument/new'));

        // Traitement des données envoyées
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getEntityManager()->persist($entity);
                $this->getEntityManager()->flush();
                $this->redirect()->toRoute('tabdocument');
            }
        }

        $view = new ViewModel([
            'entity' => $entity,
            'form' => $form,
            'roles' => $roles,
        ]);

        $view->setTemplate('oscar/tab-document/form.phtml');
        return $view;
    }

    /**
     * Suppression d'un onglet de documents
     *
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws OscarException
     */
    public function deleteAction():Response
    {
        // TODO Check avant les docs qui sont raccrochés à ce tabsDocumentsRoles
        $id = $this->params()->fromRoute('id');
        $tabDocument = $this->getEntityManager()->getRepository(TabDocument::class)->find($id);
        try {
            // Supprime les relations entre le tabDocument et les TabsDocumentsRoles
            $tabsDocumentsRoles = $tabDocument->getTabsDocumentsRoles();
            foreach ($tabsDocumentsRoles as $tabDocumentRole){
                $this->getEntityManager()->remove($tabDocumentRole);
                $this->getEntityManager()->flush();
            }
            $this->getEntityManager()->remove($tabDocument);
            $this->getEntityManager()->flush();
            return $this->redirect()->toRoute('tabdocument');
        } catch ( ForeignKeyConstraintViolationException $e ){
            throw new OscarException("Oscar n'a pas pu supprimer le type d'onglet' : " . $e->getMessage());
        }
    }

    /**
     * Modification onglet de documents
     *
     * @return ViewModel
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction():ViewModel
    {
        $entity = $this->getEntityManager()->getRepository(TabDocument::class)->find($this->params()->fromRoute('id'));
        $roles = $this->getEntityManager()->getRepository(Role::class)->findAll();
        $form = new TabDocumentForm($roles, $this->getEntityManager());
        $request = $this->getRequest();
        $form->setAttribute('action', $this->url()->fromRoute('tabdocument/edit', ['id' => $entity->getId()]));
        $form->bind($entity);

        // Traitement des données envoyées
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $this->getEntityManager()->persist($entity);
                $this->getEntityManager()->flush();
                $this->redirect()->toRoute('tabdocument');
            }
        }

        $view = new ViewModel([
            'entity' => $entity,
            'form' => $form,
            'roles' => $roles,
        ]);

        $view->setTemplate('oscar/tab-document/form.phtml');
        return $view;
    }

}// END CONTROLLER
