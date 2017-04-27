<?php
namespace UnicaenAppTest\Controller\Plugin\TestAsset;

use UnicaenAppTest\Form\TestAsset\ContactMultipageForm;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Contrôleur pour tests du plugin de contrôleur MultipageForm.
 * 
 * @method \UnicaenApp\Controller\Plugin\MultipageForm multipageForm(\UnicaenApp\Form\MultipageForm $form) Description
 */
class ContactController extends AbstractActionController
{
    const ACTIONS_PREFIX = 'ajouter-';
    
    protected $form;

    /**
     * Home action.
     * 
     * @return array
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Entry point, i.e. step 0.
     * 
     * @return Response
     */
    public function ajouterAction()
    {
//        return $this->prg();
//        return $this->multipageForm($this->getForm())->start(); // réinit du plugin et redirection vers la 1ère étape
    }

    /**
     * Step 1.
     * 
     * @return array|Response
     */
    public function ajouterIdentiteAction()
    {
//        return $this->multipageForm($this->getForm())->process();
        return array('content' => 'test');
    }

    /**
     * Step 2.
     * 
     * @return array|Response
     */
    public function ajouterAdresseAction()
    {
//        return $this->multipageForm($this->getForm())->process();
        return array('content' => 'test');
    }

    /**
     * Step 3.
     * 
     * @return array|Response
     */
    public function ajouterMessageAction()
    {
//        return $this->multipageForm($this->getForm())->process();
        return array('content' => 'test');
    }

    /**
     * Cancel action.
     * 
     * @return array|Response
     */
    public function ajouterAnnulerAction()
    {
//        return $this->redirect()->toRoute('contact', array('action' => 'index'));
        return array('content' => 'test');
    }

    /**
     * Confirmation step.
     * 
     * @return array|Response
     */
    public function ajouterConfirmerAction()
    {
//        $response = $this->multipageForm($this->getForm())->process();
//        if ($response instanceof Response) {
//            return $response;
//        }
//        return array('form' => $this->getForm());
        return array('content' => 'test');
    }

    /**
     * Final step.
     * 
     * @return array|Response
     */
    public function ajouterEnregistrerAction()
    {
        $data = $this->multipageForm($this->getForm())->getFormSessionData();
        // ...
        // enregistrement en base de données (par exemple)
        // ...
        return $this->redirect()->toRoute('home');
    }

    /**
     * Returns form.
     * 
     * @return ContactMultipageForm
     */
    public function getForm()
    {
        if (null === $this->form) {
            $this->form = new ContactMultipageForm('contact');
            $this->form->setActionPrefix(self::ACTIONS_PREFIX);
            $this->form->prepareElements();
        }
        return $this->form;
    }
    
    /**
     * Sets form.
     * 
     * @param ContactMultipageForm $form
     * @return self
     */
    public function setForm(ContactMultipageForm $form = null)
    {
        $this->form = $form;
        return $this;
    }
}