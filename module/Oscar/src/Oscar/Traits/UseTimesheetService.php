<?php
namespace Oscar\Traits;

use Oscar\Service\TimesheetService;

interface UseTimesheetService
{
    /**
     * @param PersonService $em
     */
    public function setTimesheetService( TimesheetService $s ) :void;

    /**
     * @return TimesheetService
     */
    public function getTimesheetService() :TimesheetService ;
}