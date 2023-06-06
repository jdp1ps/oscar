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
use Doctrine\ORM\Tools\Export\ExportException;
use Oscar\Entity\ContractDocument;
use Oscar\Entity\Role;
use Oscar\Entity\TabDocument;
use Oscar\Entity\TabsDocumentsRolesRepository;
use Oscar\Exception\OscarException;
use Oscar\Form\MigrateDocumentForm;
use Oscar\Form\TabDocumentForm;
use Oscar\Provider\Privileges;
use Oscar\Traits\UseContractDocumentService;
use Oscar\Traits\UseContractDocumentServiceTrait;
use Oscar\Utils\FileSystemUtils;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class TabDocumentController extends AbstractOscarController implements UseContractDocumentService
{
    use UseContractDocumentServiceTrait;

    /**
     * Accueil Page gestion onglets de documents
     * @return array
     */
    public function indexAction(): array
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_MENU_ADMIN);

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
    public function newAction(): ViewModel
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_MENU_ADMIN);

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
                $path = $this->getOscarConfigurationService()->getDocumentTabLocation($entity);
                $this->redirect()->toRoute('tabdocument');
            }
        }

        $view = new ViewModel(
            [
                'entity' => $entity,
                'form' => $form,
                'roles' => $roles,
            ]
        );

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
    public function deleteAction(): Response
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_MENU_ADMIN);

        // TODO Check avant les docs qui sont raccrochés à ce tabsDocumentsRoles avant de supprimer comme un bourrin
        $id = $this->params()->fromRoute('id');

        /** @var TabsDocumentsRolesRepository $tabDocumentRepository */
        $tabDocumentRepository = $this->getEntityManager()->getRepository(TabDocument::class);

        /** @var TabDocument $tabDocument */
        $tabDocument = $tabDocumentRepository->find($id);

        /** @var ContractDocument[] $documents */
        $documents = $tabDocumentRepository->getDocumentsForTabId($tabDocument->getId());

        try {
            // Supprime les relations entre le tabDocument et les TabsDocumentsRoles
            foreach ($documents as $document) {
                $fileFrom = $this->getOscarConfigurationService()->getDocumentRealpath($document);
                $fileTo = $this->getOscarConfigurationService()->getDocumentDropLocation()
                    . DIRECTORY_SEPARATOR . $document->getFileName();
                try {
                    FileSystemUtils::getInstance()->rename($fileFrom, $fileTo);
                } catch (\Exception $exception) {
                    throw new \Exception("Oscar n'est pas parvenu à déplacer un des documents");
                }
                $document->setTabDocument(null);
                $this->getEntityManager()->flush($document);
            }

            $tabsDocumentsRoles = $tabDocument->getTabsDocumentsRoles();
            foreach ($tabsDocumentsRoles as $tabDocumentRole) {
                $this->getEntityManager()->remove($tabDocumentRole);
            }

            $this->getEntityManager()->remove($tabDocument);
            $this->getEntityManager()->flush();
            return $this->redirect()->toRoute('tabdocument');
        } catch (\Exception $e) {
            throw new OscarException("Oscar n'a pas pu supprimer le type d'onglet' : " . $e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function migrateDocumentsAction() :array
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_MENU_ADMIN);

        $form = new MigrateDocumentForm($this->getEntityManager());

        if($this->getRequest()->isPost()){
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $documentTypeId = (int)$form->getData()['documentType'];
                $tabDocumentId = (int)$form->getData()['tabDocument'];
                if( $documentTypeId > 0 && $tabDocumentId > 0 ){
                    $this->getContractDocumentService()->migrateDocumentsTypeToTab($documentTypeId, $tabDocumentId);
                    // Traitement
                } else {
                    return $this->getResponseBadRequest("Donnèes transmises incorrecte");
                }
            }
        }
        return [
            'form' => $form
        ];
    }

    /**
     * Modification onglet de documents
     *
     * @return ViewModel
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(): ViewModel
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_MENU_ADMIN);
        $entity = $this->getEntityManager()->getRepository(TabDocument::class)
            ->find($this->params()->fromRoute('id'));
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

        $view = new ViewModel(
            [
                'entity' => $entity,
                'form' => $form,
                'roles' => $roles,
            ]
        );

        $view->setTemplate('oscar/tab-document/form.phtml');
        return $view;
    }

}// END CONTROLLER
