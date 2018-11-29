<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 25/03/16
 * Time: 15:28
 */

namespace Oscar\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oscar\Entity\Activity;
use Oscar\Entity\TraitTrackable;

/**
 * Class WorkPackage
 * @package module\Oscar\src\Oscar\Entity
 * @ORM\Entity
 */
class WorkPackage
{
    use TraitTrackable;

    /**
     * @var Activity
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="workPackages")
     */
    protected $activity;

    /**
     * @var
     * @ORM\Column(type="string", nullable=true)
     */
    protected $code;

    /**
     * @var
     * @ORM\Column(type="string")
     */
    protected $label;

    /**
     * @var
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    protected $dateStart;

    /**
     * @var datetime
     * @ORM\Column(type="date", nullable=true)
     */
    protected $dateEnd;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="WorkPackagePerson", mappedBy="workPackage", cascade={"REMOVE"})
     */
    protected $persons;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="TimeSheet", mappedBy="workpackage")
     */
    private $timesheets;

    /**
     * @return ArrayCollection
     */
    public function getTimesheets()
    {
        return $this->timesheets;
    }

    /**
     * @param ArrayCollection $timesheets
     */
    public function setTimesheets($timesheets)
    {
        $this->timesheets = $timesheets;

        return $this;
    }

    /**
     * @return Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param Activity $activity
     */
    public function setActivity($activity)
    {
        $this->activity = $activity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getDateStart()
    {
        if( $this->dateStart == null ) {
            return $this->getActivity()->getDateStart();
        }
        return $this->dateStart;
    }

    /**
     * @param datetime $dateStart
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getDateEnd()
    {
        if( $this->dateEnd == null ) {
            return $this->getActivity()->getDateEnd();
        }
        return $this->dateEnd;
    }

    /**
     * @param datetime $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;
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
     * @param ArrayCollection $persons
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;

        return $this;
    }

    public function hasPerson( Person $person ){
        foreach($this->getPersons() as $p ){
            if( $p->getPerson()->getId() == $person->getId() ){
                return true;
            }
        }
        return false;
    }



    public function __construct()
    {
        $this->persons = new ArrayCollection();
        $this->timesheets = new ArrayCollection();
    }


    public function __toString()
    {
        $project = $this->getActivity()->getProject()->getAcronym();
        $activity = $this->getActivity()->getOscarNum();
        return sprintf("%s/%s/%s %s", $project, $activity, $this->getCode(), $this->getLabel());
    }

    public function toArray(){
        $persons = [];
        $timesPersons = [];
        /** @var TimeSheet $timesheet */
        foreach( $this->getTimesheets() as $timesheet ){
            if( !array_key_exists($timesheet->getPerson()->getId(), $timesPersons) ){
                $timesPersons[$timesheet->getPerson()->getId()] = [
                    'validating' => 0,
                    'conflicts' => 0,
                    'validate' => 0,
                    'unsend' => 0
                ];
            }

            $status = $timesheet->getValidationPeriod() ? $timesheet->getValidationPeriod()->getStatus() : null;
            switch( $status ){
                case ValidationPeriod::STATUS_STEP1:
                case ValidationPeriod::STATUS_STEP2:
                case ValidationPeriod::STATUS_STEP3:
                    $timesPersons[$timesheet->getPerson()->getId()]['validating'] += $timesheet->getHours();
                    break;

                case ValidationPeriod::STATUS_CONFLICT:
                    $timesPersons[$timesheet->getPerson()->getId()]['conflicts'] += $timesheet->getHours();
                    break;

                case ValidationPeriod::STATUS_VALID:
                    $timesPersons[$timesheet->getPerson()->getId()]['validate'] += $timesheet->getHours();
                    break;

                default:
                    $timesPersons[$timesheet->getPerson()->getId()]['unsend'] += $timesheet->getHours();
            }
        }

        /** @var WorkPackagePerson $person */
        foreach( $this->getPersons() as $person ){
            $persons[] = [
                'id' => $person->getId(),
                'person' => $person->getPerson()->toArray(),
                'duration' => $person->getDuration(),
                'validating' => array_key_exists($person->getPerson()->getId(), $timesPersons) ? $timesPersons[$person->getPerson()->getId()]['validating'] : 0,
                'conflicts' => array_key_exists($person->getPerson()->getId(), $timesPersons) ? $timesPersons[$person->getPerson()->getId()]['conflicts'] : 0,
                'validate' => array_key_exists($person->getPerson()->getId(), $timesPersons) ? $timesPersons[$person->getPerson()->getId()]['validate'] : 0,
                'unsend' => array_key_exists($person->getPerson()->getId(), $timesPersons) ? $timesPersons[$person->getPerson()->getId()]['unsend'] : 0,
            ];
        }
        return [
            'id'            => $this->getId(),
            'persons'       => $persons,
            'status'        => $this->getStatus(),
            'code'          => $this->getCode(),
            'start'         => $this->getDateStart() ? $this->getDateStart()->format('Y-m-d') : "",
            'end'           => $this->getDateEnd() ? $this->getDateEnd()->format('Y-m-d') : "",
            'label'         => $this->getLabel(),
            'description'   => $this->getDescription()
        ];
    }
}