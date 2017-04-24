<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 15/07/15
 * Time: 16:55
 */

namespace UnicaenApp\Message;

use UnicaenApp\Message\Exception\MessageNotFoundException;


/**
 * Repository de Messages.
 *
 * @package UnicaenApp\Message
 */
class MessageRepository
{
    private $messages = [];

    /**
     * Construit et peuple un repository de Messages.
     *
     * @param Message[] $messages
     */
    public function __construct(array $messages)
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }
    }

    private function addMessage(Message $message)
    {
        $this->messages[$message->getId()] = $message;

        return $this;
    }

    /**
     * Recherche un Message par son id.
     *
     * @param string $messageId
     * @return Message
     */
    public function messageById($messageId)
    {
        if (!isset($this->messages[$messageId])) {
            throw new MessageNotFoundException("Message introuvable avec l'id '$messageId'.");
        }

        return $this->messages[$messageId];
    }
}