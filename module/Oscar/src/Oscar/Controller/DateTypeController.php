<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 27/01/16 14:38
 * @copyright Certic (c) 2016
 */

namespace Oscar\Controller;


use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Oscar\Entity\ActivityDate;
use Oscar\Entity\DateType;
use Oscar\Entity\DateTypeRepository;
use Oscar\Exception\OscarException;
use Oscar\Form\DateTypeForm;
use Oscar\Provider\Privileges;
use Oscar\Service\OscarUserContext;
use Oscar\Traits\UseEntityManager;
use Oscar\Traits\UseEntityManagerTrait;
use Oscar\Traits\UseOscarUserContextService;
use Oscar\Traits\UseOscarUserContextServiceTrait;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

class DateTypeController extends AbstractOscarController implements UseOscarUserContextService
{
    use UseOscarUserContextServiceTrait;

    public function indexAction()
    {
        $this->getOscarUserContextService()->check(Privileges::MAINTENANCE_MILESTONETYPE_MANAGE);


        $entities = [];
        /** @var DateType $type */
        foreach ($this->getEntityManager()->getRepository(DateType::class)->findAll() as $type) {
            $roles = [];
            foreach ($type->getRoles() as $role) {
                $roles[] = $role->getRoleId();
            }
            $entities[] = [
                'id' => $type->getId(),
                'label' => $type->getLabel(),
                'description' => $type->getDescription(),
                'facet' => $type->getFacet(),
                'finishable' => $type->isFinishable(),
                'recursivity' => $type->getRecursivity(),
                'roles' => $roles,
                'used' => 0
            ];
        }
        return [
            'entities' => $entities//$this->getEntityManager()->getRepository(DateType::class)->allWithUsage()
        ];
    }

    /**
     * Ajoute un nouveau type de jalons
     *
     * @return ViewModel
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function newAction(): ViewModel
    {
        $form = new DateTypeForm($this->getOscarUserContextService()->getOscarRoles(), $this->getEntityManager());
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

    /**
     * Supprime un jalon et la relation de ce jalon avec des roles associés
     *
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws OscarException
     */
    public function deleteAction(): Response
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

    /**
     * Modification d'un jalon
     *
     * @return ViewModel
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(): ViewModel
    {
        $entity = $this->getEntityManager()->getRepository(DateType::class)->find($this->params()->fromRoute('id'));
        $rolesCheck = $entity->getRoles();
        $arrayRoles = [];
        foreach ($rolesCheck as $role){
            $arrayRoles [] = $role->getId();
        }
        $form = new DateTypeForm($this->getOscarUserContextService()->getOscarRoles(), $this->getEntityManager(), $arrayRoles);
        $request = $this->getRequest();
        $form->setAttribute('action', $this->url()->fromRoute('datetype/edit', ['id' => $entity->getId()]));
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
