<?php
namespace UnicaenApp\View\Helper;

use UnicaenApp\Traits\MessageAwareInterface;
use UnicaenApp\Traits\MessageAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\View\Helper\Messenger;
use Zend\View\Helper\AbstractHelper;

/**
 * Aide de vue permettant de stocker une liste de messages d'information de différentes sévérités
 * et de générer le code HTML pour les afficher (affublés d'un icône correspondant à leur sévérité).
 *
 * Possibilité d'importer les messages du FlashMessenger pour les mettre en forme de la même manière.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier@unicaen.fr>
 */
class Messenger extends AbstractHelper implements MessageAwareInterface
{
    use MessageAwareTrait;

    protected $uiClasses     = [ // severity => [ alert class, icon class ]
                                 self::INFO    => ['info', 'info-sign'],
                                 self::SUCCESS => ['success', 'ok-sign'],
                                 self::WARNING => ['warning', 'warning-sign'],
                                 self::ERROR   => ['danger', 'exclamation-sign'],
    ];

    protected $severityOrder = [ // severity => order
                                 self::SUCCESS => 1,
                                 self::ERROR   => 2,
                                 self::WARNING => 3,
                                 self::INFO    => 4,
    ];

    /**
     * Activation ou non de l'affichage de l'icône
     *
     * @var bool
     */
    protected $withIcon = true;

    /**
     * @var string
     */
    protected $containerInnerTemplate = '%s';



    /**
     * Helper entry point.
     *
     * @return self
     */
    public function __invoke()
    {
        return $this;
    }



    /**
     * Retourne le code HTML généré par cette aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $exc) {
            var_dump($exc->getMessage(), \UnicaenApp\Util::formatTraceString($exc->getTraceAsString()));
            die;
        }
    }



    /**
     * Génère le code HTML.
     *
     * @return string
     */
    protected function render()
    {
        if (!$this->hasMessages()) {
            return '';
        }

        $out = '';

        foreach ($this->getSortedMessages() as $severity => $array) {
            foreach ($array as $priority => $message) {
                $out .= sprintf(
                    $this->getTemplate(is_string($severity) ? $severity : (is_string($priority) ? $priority : 'info')),
                    implode('<br />', (array)$message)
                );
            }
        }

        return $out;
    }



    /**
     * Génère le code HTML d'un seul message. Pour usage ponctuel.
     *
     * @param string $message  Message à afficher
     * @param string $severity Ex: MessageAwareInterface::INFO
     *
     * @return string
     */
    public function renderMessage($message, $severity = null)
    {
        if (!$message) {
            return '';
        }

        return sprintf($this->getTemplate($severity ?: 'info'), $message);
    }



    /**
     * @return array
     */
    private function getSortedMessages()
    {
        $messages = (array)$this->getMessages();
        $order    = $this->severityOrder;

        uksort($messages, function ($s1, $s2) use ($order) {
            if ($order[$s1] < $order[$s2]) {
                return -1;
            }
            if ($order[$s1] > $order[$s2]) {
                return 1;
            }

            return 0;
        });

        return $messages;
    }



    /**
     * Importe les messages n-1 du FlashMessenger.
     *
     * @return self
     */
    protected function importFlashMessages()
    {
        /* @var $fm \Zend\View\Helper\FlashMessenger */
        $fm = $this->getView()->getHelperPluginManager()->get('flashMessenger');

        foreach ($fm->getErrorMessages() as $message) {
            $this->addMessage($message, self::ERROR);
        }
        foreach ($fm->getSuccessMessages() as $message) {
            $this->addMessage($message, self::SUCCESS);
        }
        foreach ($fm->getInfoMessages() as $message) {
            $this->addMessage($message, self::INFO);
        }
        foreach ($fm->getWarningMessages() as $message) {
            $this->addMessage($message, self::WARNING);
        }

        return $this;
    }



    /**
     * Importe les messages courants du FlashMessenger.
     *
     * @return self
     */
    protected function importCurrentFlashMessages()
    {
        /* @var $fm \Zend\View\Helper\FlashMessenger */
        $fm = $this->getView()->getHelperPluginManager()->get('flashMessenger');
        foreach ($fm->getCurrentErrorMessages() as $message) {
            $this->addMessage($message, self::ERROR);
        }
        foreach ($fm->getCurrentSuccessMessages() as $message) {
            $this->addMessage($message, self::SUCCESS);
        }
        foreach ($fm->getCurrentInfoMessages() as $message) {
            $this->addMessage($message, self::INFO);
        }
        foreach ($fm->getCurrentWarningMessages() as $message) {
            $this->addMessage($message, self::WARNING);
        }

        /* Si on importe alors on nettoie pour éviter un deuxième affichage */
        $fm->clearCurrentMessagesFromContainer();
        return $this;
    }



    /**
     * Spécifie l'unique message courant au format Exception.
     *
     * @param \Exception $exception Exception
     * @param string     $severity  Ex: Messenger::INFO
     *
     * @return Messenger
     */
    public function setException(\Exception $exception, $severity = self::ERROR)
    {
        $message = sprintf("<p><strong>%s</strong></p>", $exception->getMessage());
        if (($previous = $exception->getPrevious())) {
            $message .= sprintf("<p>Cause :<br />%s</p>", $previous->getMessage());
        }

        return $this->setMessages([$severity => $message]);
    }



    /**
     * Importe les messages courants du FlashMessenger (remplaçant les messages existants).
     *
     * @return Messenger
     */
    public function setMessagesFromFlashMessenger()
    {
        $this->messages = [];
        $this->importFlashMessages();

        return $this;
    }



    /**
     * Importe les messages courants du FlashMessenger (remplaçant les messages existants).
     *
     * @return Messenger
     */
    public function setCurrentMessagesFromFlashMessenger()
    {
        $this->messages = [];
        $this->importCurrentFlashMessages();

        return $this;
    }



    /**
     * Ajoute les messages courants du FlashMessenger.
     *
     * @return Messenger
     */
    public function addMessagesFromFlashMessenger()
    {
        $this->importFlashMessages();

        return $this;
    }



    /**
     * Ajoute les messages courants du FlashMessenger.
     *
     * @return Messenger
     */
    public function addCurrentMessagesFromFlashMessenger()
    {
        $this->importCurrentFlashMessages();

        return $this;
    }



    /**
     * Active ou non l'affichage de l'icône.
     *
     * @param bool $withIcon <tt>true</tt> pour activer l'affichage de l'icône.
     *
     * @return Messenger
     */
    public function withIcon($withIcon = true)
    {
        $this->withIcon = (bool)$withIcon;

        return $this;
    }



    /**
     * Retourne le motif utilisé pour générer le conteneur de chaque message à afficher.
     *
     * @param string $severity    Ex: Messenger::INFO
     * @param string $containerId Dom id éventuel à utiliser pour la div englobante
     *
     * @return string
     */
    public function getTemplate($severity, $containerId = null)
    {
        if (!isset($this->uiClasses[$severity])) {
            throw new LogicException("Sévérité inconnue: " . $severity);
        }

        $innerTemplate = $this->containerInnerTemplate ?: '%s';
        $iconMarkup    = $this->withIcon ? "<span class=\"glyphicon glyphicon-{$this->uiClasses[$severity][1]}\"></span> " : null;
        $containerId   = $containerId ? sprintf('id="%s"', $containerId) : '';

        $template = <<<EOT
<div class="messenger alert alert-{$this->uiClasses[$severity][0]}" {$containerId}>
    <button type="button" class="close" title="Fermer cette alerte" data-dismiss="alert">&times;</button>
    $iconMarkup 
    $innerTemplate
</div>
EOT;

        return $template . PHP_EOL;
    }



    /**
     *
     * @param string $containerInnerTemplate
     *
     * @return self
     */
    public function setContainerInnerTemplate($containerInnerTemplate = '%s')
    {
        $this->containerInnerTemplate = $containerInnerTemplate;

        return $this;
    }
}