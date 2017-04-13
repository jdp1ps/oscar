<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 16-12-22 11:29
 * @copyright Certic (c) 2016
 */

namespace Oscar\Controller;


use Oscar\Entity\Authentification;
use Oscar\Entity\LogActivity;
use Oscar\Entity\OrganizationRole;
use Oscar\Entity\Privilege;
use Oscar\Entity\Role;
use Oscar\Provider\Privileges;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Http\Request;

class AdministrationController extends AbstractOscarController
{
    public function indexAction()
    {
        $this->getOscarUserContext()->check(Privileges::DROIT_PRIVILEGE_VISUALISATION);
        return [];
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // API
    //
    ////////////////////////////////////////////////////////////////////////////
    public function accessAPIAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        $this->getOscarUserContext()->check(Privileges::DROIT_PRIVILEGE_VISUALISATION);

        $idRolePrivilege = $this->params()->fromRoute('idroleprivilege', null);
        $method = $this->getHttpXMethod();

        if ($idRolePrivilege) {
            switch ($this->getHttpXMethod()) {

            }
        } else {
            switch ($this->getHttpXMethod()) {
                case 'PATCH':
                    $this->getOscarUserContext()->check(Privileges::DROIT_PRIVILEGE_EDITION);
                    $privilegeId = $request->getPost('privilegeid');
                    $roleId = $request->getPost('roleid');

                    /** @var Privilege $privilege */
                    $privilege = $this->getEntityManager()->getRepository(Privilege::class)->find($privilegeId);
                    if (!$privilege) {
                        return $this->getResponseBadRequest(sprintf("Le privilège %s n'existe pas/plus",
                            $privilegeId));
                    }


                    /** @var Role $role */
                    $role = $this->getEntityManager()->getRepository(Role::class)->find($roleId);
                    if (!$role) {
                        return $this->getResponseBadRequest(sprintf("Le rôle %s n'existe pas/plus",
                            $roleId));
                    }


                    if ($privilege->hasRole($role)) {
                        $privilege->removeRole($role);
                    } else {
                        $privilege->addRole($role);
                    }

                    $this->getEntityManager()->flush();

                    return $this->ajaxResponse($privilege->asArray());
                // LISTE COMPLETE
                case 'GET' :
                    $privileges = $this->getEntityManager()->getRepository(Privilege::class)->findAll();
                    $roles = $this->getEntityManager()->getRepository(Role::class)->findAll();

                    $out = [
                        'privileges' => [],
                        'roles' => []
                    ];

                    /** @var Privilege $privilege */
                    foreach ($privileges as $privilege) {
                        $out['privileges'][] = $privilege->asArray();
                    }
                    /** @var Role $role */
                    foreach ($roles as $role) {
                        $out['roles'][] = $this->getJsonRole($role);
                    }

                    return $this->ajaxResponse($out);

            }
        }

        return $this->getResponseNotImplemented(sprintf("La méthode %s n'est pas prise en charge.",
            $method));
    }

    ////////////////////////////////////////////////////////////////////////////

    public function usersAction()
    {
        $this->getOscarUserContext()->check(Privileges::DROIT_USER_VISUALISATION);
        $authenticated = $this->getEntityManager()->getRepository(Authentification::class)->findAll();
        $roleDb = $this->getEntityManager()->getRepository(Role::class)->findAll();

        $out = [
            'users' => [],
            'roles' => []
        ];

        /** @var Authentification $auth */
        foreach ($authenticated as $auth) {
            $d = $auth->toJson();
            $p = $this->getOscarUserContext()->getPersonFromAuthentification($auth);
            $person = null;
            if ($p) {
                $person = $p->toJson();
            }
            $d['person'] = $person;
            $out['users'][] = $d;
        }

        /** @var Role $role */
        foreach ($roleDb as $role) {
            $out['roles'][] = $this->getJsonRole($role);
        }

        return $out;
    }

    public function userLogsAction(){
        $this->getOscarUserContext()->check(Privileges::DROIT_USER_VISUALISATION);
        $userid = $this->params('userid');
        $logs=[];
        $activitiesLog = $this->getEntityManager()->getRepository(LogActivity::class)->findBy(['userId' => $userid],['dateCreated'=>'DESC'], 100);
        foreach( $activitiesLog as $log ){
            $logs[] = $log->toArray();
        }
       return $this->ajaxResponse($logs);
    }


    private function hydrateRolewithPost(Role &$role, Request $request)
    {
        $ldapfilter = $request->getPost('ldapFilter');

        if (trim($ldapfilter) == '') {
            $ldapfilter = null;
        }

        $role->setRoleId($request->getPost('roleId'))
            ->setLdapFilter($ldapfilter)
            ->setDescription($request->getPost('description'))
            ->setSpot($request->getPost('spot'))
            ->setPrincipal($request->getPost('principal'));
    }

    /**
     * Prépare les données pour le réstitution en JSON.
     *
     * @param Role $role
     */
    private function getJsonRole(Role $role)
    {
        $manage = $this->getOscarUserContext()->hasPrivileges(Privileges::DROIT_ROLE_EDITION);
        $datas = $role->asArray();
        $datas['editable'] = $role->getRoleId() == "Administrateur" ? false : $manage;
        $datas['deletable'] = $role->getRoleId() == "Administrateur" ? false : $manage;

        return $datas;
    }

    /**
     * Gestion/visualisation des rôles.
     *
     * @return \Zend\Http\Response|\Zend\View\Model\JsonModel
     */
    public function roleAPIAction()
    {
        $this->getOscarUserContext()->check(Privileges::DROIT_USER_VISUALISATION);
        /** @var Request $request */
        $request = $this->getRequest();
        try {
            $roleId = $this->params()->fromRoute('idrole');

            if ($roleId) {

                $this->getOscarUserContext()->hasPrivileges(Privileges::DROIT_ROLE_EDITION);

                /** @var Role $role */
                $role = $this->getEntityManager()->getRepository(Role::class)->find($roleId);
                if (!$role) {
                    return $this->getResponseBadRequest("Ce rôle n'existe plus.");
                }

                switch ($this->getHttpXMethod()) {
                    // Mise à jour
                    case "PUT" :
                        $this->hydrateRolewithPost($role, $request);
                        /** @var Role $otherRole */
                        $otherRole = $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => $role->getRoleId()]);
                        if ($role->getId() != $otherRole->getId()) {
                            return $this->getResponseBadRequest("Un rôle a déjà cet identifiant.");
                        }

                        $this->getEntityManager()->flush();
                        $this->getActivityLogService()->addUserInfo(
                            "a mis à jour le rôle " . $role->getRoleId()
                        );

                        return $this->ajaxResponse($this->getJsonRole($role));
                        break;
                    // Suppression
                    case "DELETE" :
                        $this->getActivityLogService()->addUserInfo(
                            "a supprimé le rôle " . $role->getRoleId()
                        );
                        $this->getEntityManager()->remove($role);
                        $this->getEntityManager()->flush();

                        return $this->getResponseOk("Rôle supprimé");
                        break;
                }

                return $this->getResponseBadRequest();
            } else {
                // Création
                if ($this->getHttpXMethod() == "POST") {
                    $this->getOscarUserContext()->hasPrivileges(Privileges::DROIT_ROLE_EDITION);
                    $role = $this->getEntityManager()->getRepository(Role::class)->findOneBy(['roleId' => $request->getPost('roleId')]);
                    if ($role) {
                        return $this->getResponseBadRequest(sprintf("le nom de rôle '%s' est déjà utilisé",
                            $roleId));
                    } else {
                        $role = new Role();
                        $this->hydrateRolewithPost($role, $request);
                        $this->getEntityManager()->persist($role);
                        $this->getEntityManager()->flush();

                        $this->getActivityLogService()->addUserInfo(
                            "a ajouté le rôle " . $role->getRoleId()
                        );

                        return $this->ajaxResponse($role->asArray());
                    }
                }

                return $this->getResponseNotImplemented('A FAIRE');
            }


        } catch (\Exception $e) {
            return $this->getResponseInternalError($e->getMessage());
        }

    }

    public function rolesAction()
    {
        $authenticated = $this->getEntityManager()->getRepository(Role::class)->findAll();
        $out = [];


        return ["roles" => json_encode($out)];
    }

    public function rolesEditAction()
    {
        $this->getOscarUserContext()->check(Privileges::DROIT_ROLE_EDITION);
        /** @var Request $request */
        $request = $this->getRequest();
        try {
            $role = $this->getEntityManager()->getRepository(Role::class)->find($this->params()->fromRoute('id'));
            if (!$role) {
                return $this->getResponseInternalError("Rôle inconnu");
            }
            $spot = $request->getPost('spot');
            $role->setSpot($spot);
            $this->getEntityManager()->flush();

            return $this->getResponseOk();
        } catch (\Exception $e) {
            return $this->getResponseInternalError($e->getMessage());
        }

        return $this->getResponseNotImplemented();
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    // RÔLE des ORGANISATIONS dans les ACTIVITÈS
    //
    ////////////////////////////////////////////////////////////////////////////
    public function organizationRoleAction(){
        $this->getOscarUserContext()->check(Privileges::DROIT_ROLEORGA_VISUALISATION);
        return [];
    }


    public function organizationRoleApiAction(){
        $this->getOscarUserContext()->check(Privileges::DROIT_ROLEORGA_VISUALISATION);
        $roleId = $this->params('roleid', null);
        /** @var Request $request */
        $request = $this->getRequest();
        if( $roleId == null ){
            ////////////////////////////////////////////////////////////////////
            // GET : Liste des rôles
            if( $this->getHttpXMethod() == 'GET' ){
                $roles = $this->getEntityManager()->getRepository(OrganizationRole::class)->findAll();
                $out = [];
                /** @var OrganizationRole $role */
                foreach( $roles as $role ){
                    $out[] = $role->toArray();
                }
                return $this->ajaxResponse($out);
            }
            ////////////////////////////////////////////////////////////////////
            // POST : Nouveau rôle
            elseif( $this->getHttpXMethod() == 'POST' ){
                $this->getOscarUserContext()->check(Privileges::DROIT_ROLEORGA_EDITION);
                $role = new OrganizationRole();
                $role->setLabel($request->getPost('label'))
                    ->setDescription($request->getPost('description'))
                    ->setPrincipal($request->getPost('principal') == 'true');
                $this->getEntityManager()->persist($role);
                $this->getEntityManager()->flush();
                return $this->ajaxResponse($role->toArray());
            }
        }
        else {
            $this->getOscarUserContext()->check(Privileges::DROIT_ROLEORGA_EDITION);
            $role = $this->getEntityManager()->getRepository(OrganizationRole::class)->find($roleId);
            if( !$role ){
                return $this->getResponseInternalError("Ce rôle est introuvable dans la base de données.");
            }

            if( $this->getHttpXMethod() == 'PUT' ){
                $role->setLabel($request->getPost('label'))
                    ->setDescription($request->getPost('description'))
                    ->setPrincipal($request->getPost('principal') == 'true');
                $this->getEntityManager()->persist($role);
                $this->getEntityManager()->flush();
                return $this->ajaxResponse($role->toArray());
            }
            ////////////////////////////////////////////////////////////////////
            // POST : Nouveau rôle
            elseif( $this->getHttpXMethod() == 'DELETE' ){
                $this->getEntityManager()->remove($role);
                $this->getEntityManager()->flush();
                return $this->getResponseOk('le rôle a été supprimé.');
            }
        }
        return $this->getResponseBadRequest("Accès à l'API improbable...");
    }


}