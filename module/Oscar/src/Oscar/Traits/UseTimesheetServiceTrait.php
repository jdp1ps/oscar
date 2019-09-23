<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 20/09/19
 * Time: 12:00
 */

namespace Oscar\Traits;

use Oscar\Service\TimesheetService;

trait UseTimesheetServiceTrait
{
    /**
     * @var TimesheetService
     */
    private $timesheetService;

    /**
     * @param TimesheetService $timesheetService
     */
    public function setTimesheetService( TimesheetService $timesheetService ) :void
    {
        $this->timesheetService = $timesheetService;
    }

    /**
     * @return TimesheetService
     */
    public function getTimesheetService() :TimesheetService {
        return $this->timesheetService;
    }
}