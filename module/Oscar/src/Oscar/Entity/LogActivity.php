<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/09/15 11:33
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Project
 * @ORM\Entity(repositoryClass="ActivityLogRepository")
 */
class LogActivity
{
    const LEVEL_ADMIN = 100;
    const LEVEL_INCHARGE = 200; // Responsable / chargé de valo
    const LEVEL_PRIVATE = 300; // Membres
    const LEVEL_PUBLIC = 400; // Tout le monde

    const TYPE_DEBUG = 'debug';
    const TYPE_INFO = 'info';
    const TYPE_WARN = 'warn';
    const TYPE_ERROR = 'error';
    const TYPE_CRITICAL = 'critical';

    const USER_OSCAR = -1;

    const DEFAULT_LEVEL = self::LEVEL_ADMIN;
    const DEFAULT_CONTEXT = 'Application';
    const DEFAULT_CONTEXTID = -1;
    const DEFAULT_USER = self::USER_OSCAR;
    const DEFAULT_TYPE = self::TYPE_INFO;


    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

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
    private $userId;

    /**
     * Niveau de droit (pour la consultation)
     *
     * @ORM\Column(type="integer")
     */
    private $level;

    /**
     * Type d'activité.
     *
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * IP d'origine
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $ip;


    /**
     * @ORM\Column(type="object", nullable=true)
     */
    private $datas;


    static public function getRemoteAddr()
    {
        static $_remoteAddr;
        if ($_remoteAddr === null) {
            if (php_sapi_name() == 'cli') {
                $_remoteAddr = '0.0.0.0';
            } else {
                $_remoteAddr = $_SERVER['REMOTE_ADDR'];
            }
        }
        return $_remoteAddr;
    }

    function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->context = self::DEFAULT_CONTEXT;
        $this->level = self::DEFAULT_LEVEL;
        $this->datas = null;
        $this->userId = self::DEFAULT_USER;
        $this->type = self::DEFAULT_TYPE;
        $this->ip = self::getRemoteAddr();
    }

    function __toString()
    {
        return
            $this->getDateCreated()->format('Y-m-d H:i:s')
            . " " . $this->getIp() . "@" . $this->getUserId() . ':' . "\t"
            . "[" . $this->getLevel() . ":" . $this->getType() . "] "
            . "(" . $this->getContext() . ":" . $this->getContextId() . ") "
            . $this->getMessage();
    }

    public function toArray()
    {
        return [
            'dateCreated' => $this->getDateCreated(),
            'message' => $this->getMessage(),
            'context' => $this->getContext(),
            'level' => $this->getLevel(),
            'userId' => $this->getUserId(),
            'type' => $this->getType(),
            'ip' => $this->getIp()
        ];
    }


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
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @param mixed $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
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
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
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
     * @return mixed
     */
    public function getContextId()
    {
        return $this->contextId;
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
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
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
     * @return mixed
     */
    public function getDatas()
    {
        return $this->datas;
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
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}