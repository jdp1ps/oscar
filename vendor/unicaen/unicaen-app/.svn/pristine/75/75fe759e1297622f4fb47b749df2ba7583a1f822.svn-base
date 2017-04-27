<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 23/07/15
 * Time: 11:07
 */

namespace UnicaenApp\Message;

/**
 * Classe représentant la configuration du service de gestion des messages.
 *
 * @package UnicaenApp\Message
 */
class MessageConfig
{
    private $config;

    /**
     * Constructeur privé pour obliger à passer par le normalizer de config.
     *
     * @param array $config
     */
    private function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getMessagesConfig()
    {
        return $this->config['messages'];
    }

    /**
     * Construit une instance à partir d'un normalizer de config
     * pour garantir la validité de la config.
     *
     * @param MessageConfigNormalizer $normalizer
     * @return MessageConfig
     */
    static public function create(MessageConfigNormalizer $normalizer)
    {
        return new self($normalizer->getNormalizedConfig());
    }
}