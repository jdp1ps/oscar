<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-10-10 17:12
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class NotificationPerson
 * @package Oscar\Entity
 * @ORM\Entity()
 */
class NotificationPerson
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @var Notification
     * @ORM\ManyToOne(targetEntity="Notification", inversedBy="persons")
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $notification;


    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $person;

    /**
     * Date ou la notification a été lue.
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $read;

    /**
     * NotificationPerson constructor.
     * @param $id
     */
    public function __construct()
    {
        $this->read = null;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * @param \DateTime $read
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }



}