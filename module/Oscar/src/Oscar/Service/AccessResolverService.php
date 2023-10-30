<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @copyright Certic (c) 2015
 */
namespace Oscar\Service;

use BjyAuthorize\Exception\UnAuthorizedException;
use Oscar\Entity\Activity;
use Oscar\Entity\Contexts;
use Oscar\Entity\Project;
use Oscar\Entity\ProjectMember;
use Oscar\Entity\UserAccessDefinition;
use UnicaenAuth\Service\UserContext;
use UnicaenApp\ServiceManager\ServiceLocatorAwareInterface;
use ZendTest\XmlRpc\Server\Exception;

/**
 * Permet de calculer les droits disponibles entre différents objets.
 *
 * @author Stéphane Bouvry <stephane.bouvry@unicaen.fr>
 */
class AccessResolverService implements ServiceLocatorAwareInterface
{
    use \Laminas\ServiceManager\ServiceLocatorAwareTrait;


    //////////////////////////////////////////////////////////////////// ACTIONS
    const ACTIVITY_NEW = 'ACTIVITY_NEW';

    /**
     * @return UserContext
     */
    public function getUserContext()
    {
        return $this->getServiceLocator()->get('authUserContext');
    }

    public function getActions()
    {
        return [
            $this->testUserAccessDefinition(1, Contexts::PROJECT, 'Créer un projet'),
            $this->testUserAccessDefinition(2, Contexts::PROJECT, 'Modifier un projet'),
            $this->testUserAccessDefinition(3, Contexts::PROJECT, 'Ajouter un membre'),
            $this->testUserAccessDefinition(4, Contexts::PROJECT, 'Ajouter un partenaire'),
            $this->testUserAccessDefinition(5, Contexts::PROJECT, 'Ajouter une activité'),

            $this->testUserAccessDefinition(6, Contexts::ACTIVITY, 'Créer une activité'),
            $this->testUserAccessDefinition(6, Contexts::ACTIVITY, 'Modifier la description'),
            $this->testUserAccessDefinition(6, Contexts::ACTIVITY, 'Voir les versements'),
            $this->testUserAccessDefinition(6, Contexts::ACTIVITY, 'Gérer les versements'),
            $this->testUserAccessDefinition(6, Contexts::ACTIVITY, 'Voir les dates clefs'),
            $this->testUserAccessDefinition(6, Contexts::ACTIVITY, 'Gérer les dates clefs'),
        ];
    }

    /////// TEST
    private function testUserAccessDefinition($id, $context, $label, $description=""){
        $x = new UserAccessDefinition();

        return $x->setContext($context)
            ->setDescription($description)
            ->setLabel($label);
    }



    public function checkUser( $context, $action ){
        if( $this->hasAccess($context, $action) ){
            return true;
        } else {
            throw new UnAuthorizedException();
        }
    }

    public function hasAccess( $context, $action ){
        var_dump($this->getUserContext()->getIdentity());
        var_dump($action);
        var_dump(get_class($context));
        var_dump($this->getCurrentPerson());
        return false;
    }





    private $_init;
    private $_accessList;
    private $_accessTree;
    private $_roles;

    protected function getAccessConfig()
    {
        if ($this->_init === null) {
            //........................................... CONSTRUCTION des ACCES
            $conf = $this->getServiceLocator()->get('Config');
            $this->_accessList = [];
            $this->_accessTree = [];
            $this->_roles = [];



            // Rôles disponibles
            $roles = $conf['oscar-access']['roles'];

            foreach ($roles as $roleName => $grants) {
                $roleAccess = [];
                foreach ($grants as $grant) {
                    $split = explode('/', $grant);
                    $ressource = $split[0];
                    $privilege = $split[1];
                    $regex = '/' . ($ressource == '*' ? '.*' : $ressource) . '\/' . ($privilege == '*' ? '.*' : $privilege) . '/';
                    foreach ($this->_accessList as $access) {
                        if (preg_match_all($regex, $access)) {
                            $roleAccess[] = $access;
                        }
                    }
                }
                $this->_roles[$roleName] = $roleAccess;
            }
            $this->_init = true;
            //........................................... CONSTRUCTION des ACCES
        }
    }

    protected function getAccessRole( $role ){
        if( $this->_roles === null ) {
            $conf = $this->getServiceLocator()->get('Config');
            $this->_roles = [];


            // Rôles disponibles
            $roles = $conf['oscar-access']['roles'];

            foreach ($roles as $roleName => $grants) {
                $roleAccess = [];
                foreach ($grants as $grant) {
                    $split = explode('/', $grant);
                    $ressource = $split[0];
                    $privilege = $split[1];
                    $regex = '/' . ($ressource == '*' ? '.*' : $ressource) . '\/' . ($privilege == '*' ? '.*' : $privilege) . '/';
                    foreach ($this->getAccessEnabled() as $access) {
                        if (preg_match_all($regex, $access)) {
                            $roleAccess[] = $access;
                        }
                    }
                }
                $this->_roles[$roleName] = $roleAccess;
            }
        }
        return $this->_roles[$role];
    }


    /**
     * Retourne la liste des accès possibles.
     *
     * @return array
     */
    protected function getAccessEnabled() {
        if( $this->_accessList === null ){
            $conf = $this->getServiceLocator()->get('Config');
            $this->_accessList = [];
            $declaredAccess = $conf['oscar-access']['access'];
            foreach ($declaredAccess as $context => $grant) {
                foreach ($grant as $privilege => $description) {
                    $this->_accessList[] = $context . '/' . $privilege;
                }
            }
        }
        return $this->_accessList;
    }

    /**
     * Retourne un tableau d'accès par défaut.
     *
     * @param bool $isAdmin
     *
     * @return array
     */
    public function getDefaultAccess($isAdmin = false)
    {
        $access = [];
        foreach ($this->getAccessEnabled() as $action) {
            $access[$action] = $isAdmin;
        }

        return $access;
    }

    // Internal cache
    private $_projectAccess = [];

    /**
     * retoure TRUE si l'utilisateur courant est administrateur.
     *
     * @return bool
     */
    protected function isAppAdmin()
    {
        /** @var \BjyAuthorize\Acl\Role $role */
        foreach( $this->getUserContext()->getIdentityRoles() as $role ){
            if($role->getRoleId()){
                return true;
            }
            if( $role->getParent() ){
                if( $role->getParent()->getRoleId() == 'admin' ){
                    return true;
                }
            }
        }
        return false;

    }

    public function getCurrentPerson()
    {
        if (!$this->getUserContext()) {
            throw new Exception("Impossible d'accéder au context utilisateur");
        }

        /** @var PersonService $personService */
        $personService = $this->getServiceLocator()->get('PersonService');

        return $personService->getPersonByLdapLogin($this->getUserContext()->getLdapUser()->getSupannAliasLogin());
    }

    /**
     * Construit un tableau d'accès à partir des données LDAP.
     *
     * @param Project $project
     */
    protected function getAccessFromuserContext(Project $project)
    {
        if (!$this->getUserContext()) {
            throw new Exception("Impossible d'accéder au context utilisateur");
        }


        $access = []; //self::getDefaultAccess(false);
        $userMail = $this->getUserContext()->getLdapUser()->getEmail();

        // On parse les membres du projets
        foreach ($project->getMembers() as $member) {
            if ($member->getPerson()->getEmail() == $userMail) {
                $access = array_merge($access,
                    $this->getAccessMember($project, $member));
            }
        }

        return $access;
    }

    /**
     * Construit un tableau d'accès pour une personne donnée sur le projet.
     *
     * @param Project $project
     * @param ProjectMember $member
     */
    public function getAccessMember(Project $project, ProjectMember $member)
    {
        if ($project->hasPerson($member->getPerson())) {
            return $this->getAccessRole($member->getRole());
        }
        return [];
    }

    /**
     * Calcule les droits d'accès de l'utilisateur courant sur un projet.
     *
     * @param Project $project
     *
     * @return array
     */
    public function getProjectAccess(Project $project)
    {
        if (!isset($this->_projectAccess[$project->getId()])) {
            if ($this->isAppAdmin()) {
                $access = self::getDefaultAccess(true);
            } else {
                $access = $this->getAccessFromUserContext($project);
            }
            // Calculate specifics access            
            $this->_projectAccess[$project->getId()] = $access;
        }

        return $this->_projectAccess[$project->getId()];
    }

    public function hasContextAccess( $context, $action='all')
    {
        return $this->isAppAdmin();
    }



    /**
     * Test si l'utilisateur courant a accès à l'action.
     *
     * @param type $user
     * @param Project $project
     *
     * @return type
     */
    public function allowedAction(Project $project, $action)
    {
        $access = $this->getProjectAccess($project);

        return isset($access[$action]) && $access[$action] === true;
    }

    /**
     * @deprecated
     * @param Project $project
     * @return array
     */
    public function enabledActions(Project $project)
    {
        return $this->getProjectAccess($project);
    }

    public function check(Project $project, $action)
    {
        if( $this->allowedAction($project, $action)){
            return true;
        } else {
            throw new UnAuthorizedException('Rôle insuffisant pour réaliser cette opération...');
        }
    }
}
