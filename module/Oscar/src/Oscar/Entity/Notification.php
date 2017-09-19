<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/09/15 11:33
 * @copyright Certic (c) 2015
 */
namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Notification
 * @ORM\Entity(repositoryClass="NotificationRepository")
 */
class Notification
{
    // Niveau "d'urgence"
    const LEVEL_NOTICE           = 500;
    const LEVEL_INFO             = 400;
    const LEVEL_WARN             = 300;
    const LEVEL_ERROR            = 200;
    const LEVEL_CRITICAL         = 100;

    // Context
    // Permet de charger éventuellement un objet via le context ID.
    // Permet également d'éviter l'accumulation de notifications
    // pour un même objet.
    // Exemple : Person:1024 a été modifié
    const CONTEXT_ORGANIZATION  = 'organization';
    const CONTEXT_PERSON        = 'person';
    const CONTEXT_ACTIVITY      = 'activity';
    const CONTEXT_APPLICATION   = 'application';

    // Valeurs par défaut
    const DEFAULT_LEVEL         = self::LEVEL_INFO;
    const DEFAULT_CONTEXT       = self::CONTEXT_APPLICATION;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateEffective;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @ORM\Column(type="string")
     */
    private $hash;

    /**
     * @ORM\Column(type="string")
     */
    private $context;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $contextId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $recipientId;

    /**
     * Type de notification.
     *
     * @ORM\Column(type="integer")
     */
    private $level;

    /**
     * Type de notification.
     *
     * @ORM\Column(type="boolean")
     */
    private $read;

    /**
     * @ORM\Column(type="object", nullable=true)
     */
    private $datas;

    function __construct()
    {
        $this->dateCreated  = new \DateTime();
        $this->context      = self::DEFAULT_CONTEXT;
        $this->datas        = null;
        $this->level        = self::DEFAULT_LEVEL;
        $this->read         = false;
    }

    ////////////////////////////////////////////////////////////////////////////
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateEffective()
    {
        return $this->dateEffective;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return mixed
     */
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     * @return mixed
     */
    public function getRecipientId()
    {
        return $this->recipientId;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return mixed
     */
    public function getDatas()
    {
        return $this->datas;
    }

    /**
     * @return boolean
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * @return boolean
     */
    public function isRead()
    {
        return $this->read;
    }

    /**
     * @param mixed $dateEffective
     */
    public function setDateEffective($dateEffective)
    {
        $this->dateEffective = $dateEffective;

        return $this;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param mixed $context
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @param mixed $contextId
     */
    public function setContextId($contextId)
    {
        $this->contextId = $contextId;

        return $this;
    }

    /**
     * @param mixed $recipientId
     */
    public function setRecipientId($recipientId)
    {
        $this->recipientId = $recipientId;

        return $this;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @param mixed $datas
     */
    public function setDatas($datas)
    {
        $this->datas = $datas;

        return $this;
    }

    /**
     * @param mixed $read
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    ////////////////////////////////////////////////////////////////////////////



    function __toString()
    {
        return
            $this->getDateEffective()->format('Y-m-d H:i:s')
            ."@".$this->getRecipientId()."\t"
            ."[".$this->getLevel()."]"
            ."(".$this->getContext().":".$this->getContextId().") "
            .$this->getMessage()
            ;
    }

    public function toArray(){
        return [
            'id' =>  $this->getId(),
            'dateEffective' =>  $this->getDateEffective()->format('Y-m-d H:i:s'),
            'message' => $this->getMessage(),
            'context' =>  $this->getContext(),
            'contextId' =>  $this->getContextId(),
            'level' =>  $this->getLevel(),
            'recipientId' =>  $this->getRecipientId(),
            'level' =>  $this->getLevel(),
            'read' => $this->isRead()
        ];
    }
}