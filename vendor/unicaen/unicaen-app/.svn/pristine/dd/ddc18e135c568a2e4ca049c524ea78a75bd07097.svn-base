<?php

namespace UnicaenApp\Message\View\Helper;

use UnicaenApp\Message\MessageService;
use Zend\View\Helper\AbstractHelper;

/**
 * Aide de vue permettant d'obtenir le texte d'un message.
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class MessageHelper extends AbstractHelper
{
    private $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Retourne le texte d'un message, pour le contexte spécifié, et incrusté des paramètres spécifiés.
     *
     * @param string $messageId
     * @param array $parameters
     * @param mixed $context
     * @return string
     */
    public function render($messageId, array $parameters = [], $context = null)
    {
        return $this->messageService->render($messageId, $parameters, $context);
    }
}