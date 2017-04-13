<?php

namespace Oscar\Controller;

use Oscar\Entity\ActivityPerson;
use Oscar\Entity\Person;
use Oscar\Service\OscarUserContext;
use Zend\Mvc\Application;
use Zend\View\Model\ViewModel;

/**
 * @author  Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 */
class PublicController extends AbstractOscarController
{
    public function accessAction()
    {
        $accessResolverService = $this->getAccessResolverService();
        $actions = $accessResolverService->getActions();
        return [
            'actions'   => $actions,
            'roles'     => ActivityPerson::getRoles(),
        ];
    }

    /**
     * Page d'accueil.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $person = null;

        /*
        $this->flashMessenger()->addErrorMessage("AN ERROR");
        $this->flashMessenger()->addInfoMessage("AN INFO");
        $this->flashMessenger()->addMessage("A MESSAGE");
        $this->flashMessenger()->addSuccessMessage("A SUCCESS");
        $this->flashMessenger()->addWarningMessage("A WARNING");
        /****/

        try {
            $person = $this->getOscarUserContext()->getCurrentPerson();
        } catch( \Exception $e ){

        }
        return [
            'user' => $person
        ];
    }

    public function changelogAction()
    {
        $parser = new \Parsedown();
        return [
            'content'   => $parser->text(file_get_contents(getcwd().'/changelog-public.md'))
        ];
    }
    
    protected function getSuperView($message)
    {
        $view = new ViewModel(['message'=>$message]);
        $view->setTemplate('/none');
        return $view;
    }

    public function forAllAction()
    {
        return $this->getSuperView('For All');
    }
    
    public function forUserAction()
    {
        return $this->getSuperView('For User');
    }
    
    public function forAdminAction()
    {
        return $this->getSuperView('For Admin');
    }

    public function documentationAction()
    {
        $doc = $this->params()->fromRoute('doc');
        if( $doc ){
            return [
                'content' => "super doc"
            ];
        }
        return [];
    }
}
