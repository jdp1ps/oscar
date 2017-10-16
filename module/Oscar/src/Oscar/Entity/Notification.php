<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 07/09/15 11:33
 * @copyright Certic (c) 2015
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Notification
 * @ORM\Entity(repositoryClass="NotificationRepository")
 */
class Notification
{
    // Niveau "d'urgence"
    const LEVEL_NOTICE = 500;
    const LEVEL_INFO = 400;
    const LEVEL_WARN = 300;
    const LEVEL_ERROR = 200;
    const LEVEL_CRITICAL = 100;

    // Context
    // Permet de charger éventuellement un objet via le context ID.
    // Permet également d'éviter l'accumulation de notifications
    // pour un même objet.
    // Exemple : Person:1024 a été modifié
    const OBJECT_ORGANIZATION = 'organization';
    const OBJECT_PERSON = 'person';
    const OBJECT_ACTIVITY = 'activity';
    const OBJECT_APPLICATION = 'application';

    // Valeurs par défaut
    const DEFAULT_LEVEL = self::LEVEL_INFO;
    const DEFAULT_CONTEXT = self::OBJECT_APPLICATION;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Date à partir de laquelle la notification s'affiche.
     *
     * @ORM\Column(type="date", nullable=false)
     */
    private $dateEffective;

    /**
     * Date réél de l'événement lié à la notification.
     *
     * @ORM\Column(type="date", nullable=false)
     */
    private $dateReal;


    /**
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * Le type d'objet attaché (Activity, Person, etc...)
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $object;

    /**
     * L'identifiant de cet objet dans la BDD.
     * @var integer
     * @ORM\Column(type="integer", nullable=true)
     */
    private $objectId;

    /**
     * @ORM\Column(type="string")
     */
    private $hash;

    /**
     * @ORM\Column(type="string")
     */
    private $context;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $serie;


    /**
     * Type de notification.
     *
     * @ORM\Column(type="integer")
     */
    private $level;

    /**
     * @ORM\Column(type="object", nullable=true)
     */
    private $datas;

    /**
     * Lots de travail
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="NotificationPerson", mappedBy="notification", cascade={"remove"})
     * ORM\OrderBy({"code" = "ASC"})
     */
    private $persons;

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
    public function getDateEffective()
    {
        return $this->dateEffective;
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
     * @return ArrayCollection
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @param NotificationPerson $notificationPerson
     * @return $this
     */
    public function addNotificationPerson(NotificationPerson $notificationPerson)
    {
        if( !$this->persons->contains($notificationPerson) ){
            $this->persons->add($notificationPerson);
        }
        return $this;
    }

    /**
     * @param Person $person
     * @return $this|null
     */
    public function addPerson( Person $person, EntityManager $em ){
        /** @var NotificationPerson $notificationPerson */
        foreach ( $this->persons as $notificationPerson ){
            if( $notificationPerson->getPerson() == $person ){
                $notificationPerson->setRead(null);
                return null;
            }
        }
        $n = new NotificationPerson();
        $em->persist($n);
        return $n->setNotification($this)->setPerson($person);
    }

    /**
     * @param Person $person
     * @return $this|null
     */
    public function addPersons( array $persons, EntityManager $em ){
        /** @var Person $p */
        foreach ( $persons as $p ){
            $this->addPerson($p, $em);
        }
        return $this;
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
     * @return string
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $object
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
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
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * @param mixed $serie
     */
    public function setSerie($serie)
    {
        $this->serie = $serie;

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
    public function getDateReal()
    {
        return $this->dateReal;
    }

    /**
     * @param mixed $dateReal
     */
    public function setDateReal($dateReal)
    {
        $this->dateReal = $dateReal;

        return $this;
    }


    function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->context = self::DEFAULT_CONTEXT;
        $this->datas = null;
        $this->level = self::DEFAULT_LEVEL;
        $this->persons = new ArrayCollection();
    }

    ////////////////////////////////////////////////////////////////////////////


    ////////////////////////////////////////////////////////////////////////////


    function __toString()
    {
        return
            $this->getDateEffective()->format('Y-m-d') . ' / '
            . $this->getDateReal()->format('Y-m-d')
            . " " . $this->getObject() . ":" . $this->getObjectId() . "\t"
            . "[" . $this->getContext() . "]"
            . $this->getMessage();
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'dateEffective' => $this->getDateEffective()->format('Y-m-d'),
            'dateReal' => $this->getDateReal()->format('Y-m-d'),
            'message' => $this->getMessage(),
            'object' => $this->getObject(),
            'objectId' => $this->getObjectId(),
            'context' => $this->getContext(),
            'level' => $this->getLevel(),
            'serie' => $this->getSerie(),
        ];
    }
}