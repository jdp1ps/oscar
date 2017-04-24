<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 23/07/15
 * Time: 16:03
 */

namespace UnicaenApp\Message;

use UnicaenApp\Message\Exception\ConfigException;
use UnicaenApp\Message\Specification\IsEqualSpecification;
use UnicaenApp\Message\Specification\MessageSpecificationInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Classe chargée de valider et normaliser les données de configuration du service de fourniture de messages.
 *
 * @package UnicaenApp\Message
 */
class MessageConfigNormalizer
{
    private $config;
    private $normalizedConfig;
    private $normalized = false;
    private $serviceLocator;

    /**
     * @param ServiceLocatorInterface $serviceLocator Utile pour les spécifications injectées dans le service manager
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Spécifie la config à normaliser.
     *
     * @param mixed $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config     = $config;
        $this->normalized = false;

        return $this;
    }

    /**
     * Normalize config.
     *
     * @return array Normalized config
     */
    public function getNormalizedConfig()
    {
        if ($this->normalized === false) {
            $this->normalizeConfig();
        }

        return $this->normalizedConfig;
    }

    private function normalizeConfig()
    {
        if (! is_array($this->config)) {
            throw new ConfigException("La configuration doit être spécifiée sous la forme d'un tableau.");
        }
        if (isset($this->config['messages']) && ! is_array($this->config['messages'])) {
            throw new ConfigException("Les messages spécifiés dans la configuration (clé 'messages') doivent l'être sous la forme d'un tableau.");
        }

        $this->normalizedConfig = $this->config;

        if (! isset($this->normalizedConfig['messages'])) {
            $this->normalizedConfig['messages'] = [];
        }

        foreach ($this->normalizedConfig['messages'] as $index => $configRow) {
            $this->assertConfigRowIsValid($configRow);
            $this->assertMessageIdIsValid($configRow['id']);
            $this->assertMessageDataIsValid($configRow['data']);

            //$this->normalizedConfig['messages'][$index]['id']   = $this->getNormalizedMessageId($configRow['id']);
            $this->normalizedConfig['messages'][$index]['data'] = $this->getNormalizedMessageData($configRow['data']);
        }

        $this->normalized = true;

        return $this;
    }

    private function getNormalizedMessageData($data)
    {
        $normalizedData = $data;

        foreach ($data as $text => $specification) {
            $normalizedData[$text] = $this->normalizedSpecification($specification);
        }

        return $normalizedData;
    }

    private function normalizedSpecification($specification)
    {
        // un callable est préservé.
        if (is_callable($specification)) {
            return $specification;
        }

        // une spécification de type MessageSpecificationInterface est préservée.
        if ($specification instanceof MessageSpecificationInterface) {
            return $specification;
        }

        // 'true' est une spécification particulière pour le texte par défaut, elle est préservée.
        if ($specification === true) {
            return $specification;
        }

        // Si la spécification est le nom d'un service connu du service locator,
        // la spécification devient le service lui-même.
        if ($this->serviceLocator->has($specification)) {
            return $this->serviceLocator->get($specification);
        }

        // Sinon, on transforme la spécification en IsEqualSpecification
        return new IsEqualSpecification($specification);
    }

    private function assertConfigRowIsValid($configRow)
    {
        if (! isset($configRow['id'])) {
            throw new ConfigException(
                "Chaque ligne de config doit avoir une clé 'id' pour l'identifiant unique du message.");
        }
        if (! isset($configRow['data'])) {
            throw new ConfigException(
                "Chaque ligne de config doit avoir une clé 'data' pour les textes=>spécifications du message.");
        }
    }

    private function assertMessageIdIsValid($messageId)
    {
        if (! is_string($messageId)) {
            throw new ConfigException(sprintf(
                "L'identifiant de message spécifié n'est pas une chaîne de caractères: '%s'.",
                $messageId
            ));
        }
    }

    private function assertMessageDataIsValid($messageData)
    {
        if (! is_array($messageData)) {
            throw new ConfigException("Les data de message doivent être spécifiées sous forme d'un tableau.");
        }
        if (! count($messageData)) {
            throw new ConfigException("Les data de message ne peuvent être vides.");
        }
    }
}