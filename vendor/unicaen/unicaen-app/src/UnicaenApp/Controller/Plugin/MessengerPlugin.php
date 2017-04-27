<?php
namespace UnicaenApp\Controller\Plugin;

use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenApp\Traits\MessageAwareTrait;
use UnicaenApp\View\Helper\Messenger as MessengerViewHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Aide de vue permettant de stocker une liste de messages d'information de différentes sévérités 
 * et de générer le code HTML pour les afficher (affublés d'un icône correspondant à leur sévérité). 
 * 
 * Possibilité d'importer les messages du FlashMessenger pour les mettre en forme de la même manière.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class MessengerPlugin extends AbstractPlugin implements ServiceLocatorAwareInterface, MessageAwareInterface
{
    use ServiceLocatorAwareTrait;
    use MessageAwareTrait;
    
    /**
     * @var MessengerViewHelper
     */
    protected $messengerViewHelper;

    /**
     * Helper entry point.
     *
     * @return MessengerViewHelper
     */
    public function __invoke()
    {
        return $this->getMessengerViewHelper();
    }
    
    /**
     * @return MessengerViewHelper
     */
    private function getMessengerViewHelper()
    {
        if (null === $this->messengerViewHelper) {
            $sl = $this->getServiceLocator()->getServiceLocator();
            $this->messengerViewHelper = $sl->get('viewhelpermanager')->get('messenger');
        }
        
        return $this->messengerViewHelper;
    }


}