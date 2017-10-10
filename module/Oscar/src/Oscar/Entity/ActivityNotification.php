<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-10-10 14:48
 * @copyright Certic (c) 2017
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;

/** !!!REBUS!!! **/
class ActivityNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Activité de la notification.
     * @var Activity
     * @ORM\ManyToOne(targetEntity="Activity")
     */
    private $activity;

    /**
     * Message affiché.
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $message;

    /**
     * Contexte pour éviter la multiplication des notifications.
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $context;


    /**
     * Date effective, date à partir de laquelle s'affiche cette notification.
     * @var datetime
     * @ORM\Column(type="date", nullable=false)
     */
    private $dateEffective;


    /**
     * Clef pour le stack.
     *
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false, unique=true)
     */
    private $key;

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @param mixed $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;

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
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @param datetime $dateEffective
     */
    public function setDateEffective($dateEffective)
    {
        $this->dateEffective = $dateEffective;

        return $this;
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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return datetime
     */
    public function getDateEffective()
    {
        return $this->dateEffective;
    }


}