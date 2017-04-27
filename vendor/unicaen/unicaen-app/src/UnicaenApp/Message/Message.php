<?php
/**
 * Created by PhpStorm.
 * User: gauthierb
 * Date: 16/07/15
 * Time: 11:03
 */

namespace UnicaenApp\Message;

use UnicaenApp\Exception\LogicException;
use UnicaenApp\Message\Exception\ConfigException;
use UnicaenApp\Message\Exception\MessageTextNotFoundException;
use UnicaenApp\Message\Specification\MessageSpecificationInterface;


/**
 * Classe représentant l'idée d'un Message dont le texte dépend d'un contexte extérieur.
 *
 * Un message possède :
 * - un id ;
 * - différents "textes" possibles associés chacun à une "spécification" unique.
 *
 * Une "spécification" est chargée de retourner <code>true</code> lorsqu'elle est satisfaite par le contexte
 * qu'on lui fournit.
 * La première spécification satisfaite désigne le texte du message à fournir au monde extérieur.
 *
 * @package UnicaenApp\Message
 */
class Message
{
    private $id;
    private $specificationsForTexts = [];
    private $contextApplied = false;
    private $textOfSatisfiedSpecification;
    private $satisfiedSpecification;
    private $satisfiedSpecificationSentBackData = [];

    /**
     * @param string $id
     * @param array $specificationsForTexts
     */
    public function __construct($id, array $specificationsForTexts)
    {
        $this
            ->setId($id)
            ->loadSpecificationsForTexts($specificationsForTexts);
    }

    /**
     * @param array $specificationsForTexts
     * @return $this
     */
    private function loadSpecificationsForTexts(array $specificationsForTexts)
    {
        if (! $specificationsForTexts) {
            throw new ConfigException("Aucune donnée spécifiée.");
        }

        foreach ($specificationsForTexts as $text => $specification) {
            $this->setSpecificationForText($specification, $text);
        }

        return $this;
    }

    /**
     * @param callable $specification
     * @param string $text
     * @return $this
     */
    private function setSpecificationForText($specification, $text)
    {
        $this->assertTextIsValid($text);
        $this->assertSpecificationIsValid($specification);

        $this->specificationsForTexts[$text] = $specification;

        return $this;
    }

    private function assertIdIsValid($messageId)
    {
        if (!is_string($messageId)) {
            throw new ConfigException("L'identifiant d'une message doit être une chaîne de caractère.");
        }
    }

    private function assertTextIsValid($text)
    {
        if (!is_string($text)) {
            throw new ConfigException("Le texte d'un message doit être une chaîne de caractère.");
        }
    }

    protected function assertSpecificationIsValid($specification)
    {
        if (is_callable($specification)) {
            return;
        }
        if ($specification === true) {
            return;
        }
        if ($specification instanceof MessageSpecificationInterface) {
            return;
        }

        throw new ConfigException(
            "La spécification d'un message doit être " .
            "soit un callable, " .
            "soit l'instance d'une classe implémentant MessageSpecificationInterface, " .
            "soit le booléen TRUE (pour le texte par défaut).");
    }

    /**
     * @param mixed $context
     * @return $this
     */
    public function applyContext($context)
    {
        foreach ($this->specificationsForTexts as $text => $specification) {
            $specificationIsSatisfied = $this->evalSpecificationAndCollectSentBackData($specification, $context, $text);
            if (true === $specificationIsSatisfied) {
                $this->textOfSatisfiedSpecification = $text;
                $this->satisfiedSpecification       = $specification;
                break;
            }
        }

        $this->contextApplied = true;

        return $this;
    }

    /**
     * Retourne le texte du message pour un contexte donné.
     *
     * @return string
     */
    public function getTextForContext()
    {
        if (! $this->contextApplied) {
            throw new LogicException(sprintf("La méthode %s::applyContext doit être appelée au préalable.", __CLASS__));
        }

        if (null === $this->textOfSatisfiedSpecification) {
            throw new MessageTextNotFoundException(sprintf(
                "Aucun texte trouvé pour le message '%s' et le contexte spécifié.",
                $this->getId()/*,
                is_object($context) ? get_class($context) : print_r($context, true)*/
            ));
        }

        return $this->textOfSatisfiedSpecification;
    }

    /**
     * @param mixed $specification
     * @param mixed $context
     * @param string $text
     * @return bool
     */
    protected function evalSpecificationAndCollectSentBackData($specification, $context, $text)
    {
        $isSatisfied  = $specification;
        $sentBackData = [];

        if (is_callable($specification)) {
            $isSatisfied = $specification($context, $sentBackData);
        }
        elseif ($specification instanceof MessageSpecificationInterface) {
            $isSatisfied = $specification->isSatisfiedBy($context, $sentBackData);
        }

        $this->satisfiedSpecificationSentBackData = $sentBackData;

        if (! is_bool($isSatisfied)) {
            throw new ConfigException(sprintf(
                "La spécification associée au texte \"%s\" du message '%s' doit retourner un booléen.",
                $text,
                $this->getId()
            ));
        }

        return $isSatisfied;
    }

    /**
     * Retourne les données éventuellement retournées par l'exécution de la spécification.
     *
     * @return array
     */
    public function getSatisfiedSpecificationSentBackData()
    {
        return $this->satisfiedSpecificationSentBackData;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    private function setId($id)
    {
        $this->assertIdIsValid($id);
        $this->id = $id;

        return $this;
    }

    /**
     * Construit des Messages à partir d'un tableau de données.
     *
     * @param array $config Format :
     * <pre>
     *  [
     *      [
     *          'id' => id,
     *          'data' => [
     *              text => specification,
     *              ...
     *          ]
     *      ],
     *      ...
     *  ]
     * </pre>
     * @return array
     */
    static public function createInstancesFromConfig(array $config)
    {
        $instances = [];

        foreach ($config as $array) {
            $messageId   = $array['id'];
            $messageData = $array['data'];

            $instances[$messageId] = new self($messageId, $messageData);
        }

        return $instances;
    }
}