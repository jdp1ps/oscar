<?php

namespace UnicaenAuth\Controller;

use UnicaenAuth\Entity\Db\Privilege;
use UnicaenAuth\Entity\Db\Role;
use UnicaenAuth\Form\Droits\Traits\RoleFormAwareTrait;
use UnicaenAuth\Service\Traits\PrivilegeServiceAwareTrait;
use UnicaenAuth\Service\Traits\RoleServiceAwareTrait;
use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


/**
 * Description of DroitsController
 *
 *
 * @author Laurent LÉCLUSE <laurent.lecluse at unicaen.fr>
 */
class DroitsController extends AbstractActionController
{
    use RoleServiceAwareTrait;
    use RoleFormAwareTrait;
    use PrivilegeServiceAwareTrait;



    /**
     *
     * @return type
     */
    public function indexAction()
    {
        return [];
    }



    public function rolesAction()
    {
        $roles = $this->getServiceRole()->getList();

        return compact('roles');
    }



    public function roleEditionAction()
    {
        $roleId = $this->params()->fromRoute('role');
        $role   = $this->getServiceRole()->get($roleId);
        $errors = [];

        $form = $this->getFormDroitsRole();
        if (empty($role)) {
            $title = 'Création d\'un nouveau rôle';
            $role  = $this->getServiceRole()->newEntity();
            $form->setObject($role);
        } else {
            $title = 'Édition du rôle';
            $form->bind($role);
        }
        $form->setAttribute('action', $this->url()->fromRoute(null, [], [], true));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                try {
                    $this->getServiceRole()->save($role);
                    $form->get('id')->setValue($role->getId()); // transmet le nouvel ID
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return compact('form', 'title', 'errors');
    }



    public function roleSuppressionAction()
    {
        $roleId = $this->params()->fromRoute('role');
        $role   = $this->getServiceRole()->get($roleId);

        $title  = "Suppression du rôle";
        $form   = $this->getFormSupprimer();
        $errors = [];

        if ($this->getRequest()->isPost()) {
            try {
                $this->getServiceRole()->delete($role);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return compact('role', 'title', 'form', 'errors');
    }



    public function privilegesAction()
    {
        $ps         = $this->getServicePrivilege()->getList();
        $privileges = [];
        foreach ($ps as $privilege) {
            $categorie = $privilege->getCategorie();
            if (!isset($privileges[$categorie->getCode()])) {
                $privileges[$categorie->getCode()] = [
                    'categorie'  => $categorie,
                    'privileges' => [],
                ];
            }
            $privileges[$categorie->getCode()]['privileges'][] = $privilege;
        }

        $roles = $this->getServiceRole()->getList();

        return compact('privileges', 'roles');
    }



    public function privilegesModifierAction()
    {
        $roleId = $this->params()->fromPost('role');
        $role   = $this->getServiceRole()->get($roleId);

        $privilegeId = $this->params()->fromPost('privilege');
        $privilege   = $this->getServicePrivilege()->get($privilegeId);

        $action    = $this->params()->fromPost('action');

        switch ($action) {
            case 'accorder':
                $this->getServicePrivilege()->addRole($privilege,$role);
                break;
            case 'refuser':
                $this->getServicePrivilege()->removeRole($privilege,$role);
                break;
        }

        $viewModel = new ViewModel();
        $viewModel->setVariables(compact('role', 'privilege'))
            ->setTerminal(true);

        return $viewModel;
    }



    public function getFormSupprimer()
    {
        $form = new Form();
        $form->add([
            'name' => 'id',
            'type' => 'Hidden',
        ]);
        $form->add([
            'name' => 'submit',
            'type'  => 'Submit',
            'attributes' => [
                'value' => 'Je confirme la suppression',
            ],
        ]);
        $form->setAttribute('action', $this->url()->fromRoute(null, [], [], true));
        return $form;
    }
}