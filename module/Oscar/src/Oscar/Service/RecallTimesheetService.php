<?php


namespace Oscar\Service;


use Oscar\Entity\Person;

class RecallTimesheetService
{
    private $timesheetService;

    public function getTimesheetService(){
        return $this->timesheetService;
    }

    public function __construct( TimesheetService $timesheetService)
    {
        $this->timesheetService = $timesheetService;
    }

    /**
     * Détermine si la personne est considérée comme déclarant pour la période.
     *
     * @param Person $person
     * @param $period
     */
    public function isDeclarerAt(Person $person, $period)
    {

    }

    public function getRecallPersonAt(Person $person, $period)
    {

    }

    public function autoRecallPersonAt(Person $person, $period)
    {

    }

    public function getDeclarerPeriods(Person $person)
    {

    }
}