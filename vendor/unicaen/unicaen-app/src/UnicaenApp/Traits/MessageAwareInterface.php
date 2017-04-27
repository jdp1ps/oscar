<?php

namespace UnicaenApp\Traits;

/**
 * 
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
interface MessageAwareInterface
{
    const INFO    = 'info';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const ERROR   = 'danger';
    
    /**
     * Spécifie les messages courants (remplaçant les messages existants).
     * 
     * @param string|array $messages
     * @return self
     */
    public function setMessages($messages);
    
    /**
     * Spécifie l'unique message courant.
     * 
     * @param string $message Message
     * @param string $severity Ex: self::INFO
     * @return self
     */
    public function setMessage($message, $severity = null);
    
    /**
     * Indique si cette aide de vue contient des messages actuellement.
     * 
     * @param string $severity Seule sévérité éventuelle à prendre en compte, ex: self::INFO
     * @return bool
     */
    public function hasMessages($severity = null);
    
    /**
     * Retourne les messages courants.
     * 
     * @param string $severity Seule sévérité éventuelle à prendre en compte, ex: self::INFO
     * @return array
     */
    public function getMessages($severity = null);
    
    /**
     * Ajoute un message.
     * 
     * @param string $message Message
     * @param string $severity Sévérité, ex: self::INFO
     * @return self
     */
    public function addMessage($message, $severity = null);
    
    /**
     * Ajoute plusieurs messages.
     * 
     * @param array $messages [Sévérité => Message]
     * @return self
     */
    public function addMessages($messages);
    
    /**
     * Supprime tous les messages courants.
     * 
     * @param string $severity Seule sévérité éventuelle à prendre en compte, ex: self::INFO
     * @return self
     */
    public function clearMessages($severity = null);
}