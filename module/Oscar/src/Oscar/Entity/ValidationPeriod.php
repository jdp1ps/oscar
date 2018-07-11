<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 11/07/18
 * Time: 16:55
 */

namespace Oscar\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @package Oscar\Entity
 * @ORM\Entity
 */
class ValidationPeriod
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Activity")
     */
    private $activity;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Person")
     */
    private $person;


    /**
     * Mois
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private $month;

    /**
     * Année
     *
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private $year;

    /**
     * @return mixed
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param mixed $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;
    }

    /**
     * @return mixed
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param mixed $person
     */
    public function setPerson($person)
    {
        $this->person = $person;
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param int $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }




}