<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 15/07/15
 * Time: 17:46
 */

namespace UnicaenApp\Message;


/**
 * Guichet d'obtention des textes de message, selon le contexte spécifié.
 *
 * @package UnicaenApp\Message
 */
class MessageService
{
    /**
     * @var MessageRepository
     */
    private $messageRepo;

    private $context;

    /**
     * @param MessageRepository $messageRepo
     */
    public function __construct(MessageRepository $messageRepo)
    {
        $this->messageRepo = $messageRepo;
    }

    /**
     * Retourne le texte d'un message, correspondant au contexte courant, et incrusté des paramètres spécifiés.
     *
     * @param string $messageId
     * @param array $parameters
     * @param mixed $substitutionContext
     * @return string
     */
    public function render($messageId, array $parameters = [], $substitutionContext = null)
    {
        $context = $substitutionContext !== null ?
            $substitutionContext :
            $this->getContext();

        $message = $this->messageRepo->messageById($messageId);
        $message->applyContext($context);

        return MessageFormatter::format($message, $parameters);
    }

    /**
     * Spécifie le contexte à prendre en compte.
     *
     * @param mixed $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Retourne le contexte courant.
     *
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }
}