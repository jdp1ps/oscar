<?php

namespace UnicaenApp\Traits;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
trait MessageAwareTrait
{
    /**
     * @var array 
     */
    protected $messages = [];
    
    /**
     * Spécifie les messages courants (remplaçant les messages existants).
     * 
     * @param string|array $messages
     * @return self
     */
    public function setMessages($messages)
    {
        $this->messages = array();
        foreach ((array)$messages as $severity => $message) {
            $this->addMessage($message, $severity);
        }
        return $this;
    }
    
    /**
     * Spécifie l'unique message courant.
     * 
     * @param string $message Message
     * @param string $severity Ex: MessageAwareInterface::INFO
     * @return self
     */
    public function setMessage($message, $severity = null)
    {
        return $this->setMessages(array($severity => $message));
    }
    
    /**
     * Indique si cette aide de vue contient des messages actuellement.
     * 
     * @param string $severity Seule sévérité éventuelle à prendre en compte, ex: MessageAwareInterface::INFO
     * @return bool
     */
    public function hasMessages($severity = null)
    {
        return (boolean)$this->getMessages($severity);
    }
    
    /**
     * Retourne les messages courants.
     * 
     * @param string $severity Seule sévérité éventuelle à prendre en compte, ex: MessageAwareInterface::INFO
     * @return array
     */
    public function getMessages($severity = null)
    {
        if ($severity && array_key_exists($severity, $this->messages)) {
            return $this->messages[$severity];
        }
        return $this->messages;
    }
    
    /**
     * Retourne les messages courants en une seule chaîne de caractères.
     * 
     * @param string $glue Séparateur à utiliser (PHP_EOL par défaut)
     * @param string $severity Seule sévérité éventuelle à prendre en compte, ex: MessageAwareInterface::INFO
     * @return string
     */
    public function getMessage($glue = PHP_EOL, $severity = null)
    {
        $messages = $this->getMessages($severity);
        
        return implode($glue, \UnicaenApp\Util::extractArrayLeafNodes($messages));
    }
    
    /**
     * Ajoute un message.
     * 
     * @param string $message Message
     * @param string $severity Sévérité, ex: MessageAwareInterface::INFO
     * @return self
     */
    public function addMessage($message, $severity = null, $priority = 1)
    {
        if (!$severity || !is_string($severity)) {
            $severity = MessageAwareInterface::INFO;
        }
        if (!isset($this->messages[$severity])) {
            $this->messages[$severity] = [];
        }
        if (!in_array($message, $this->messages[$severity])) {
            while (array_key_exists($priority, $this->messages[$severity])) { // recherche priorité disponible
                $priority++;
            }
            $this->messages[$severity][$priority] = $message;
            ksort($this->messages[$severity]);
        }
        return $this;
    }
    
    /**
     * Ajoute plusieurs messages.
     * 
     * @param array $messages [Sévérité => Message]
     * @return self
     */
    public function addMessages($messages)
    {
        foreach ($messages as $severity => $message) {
            $this->addMessage($message, $severity);
        }
        
        return $this;
    }
    
    /**
     * Supprime tous les messages courants.
     * 
     * @param string $severity Seule sévérité éventuelle à prendre en compte, ex: MessageAwareInterface::INFO
     * @return self
     */
    public function clearMessages($severity = null)
    {
        if ($severity && array_key_exists($severity, $this->getMessages())) {
            $this->messages[$severity] = array();
        }
        else {
            $this->messages = array();
        }
        return $this;
    }
}