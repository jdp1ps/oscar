<?php

namespace Oscar\Formatter\Data;

class TimesheetPersonPeriodData
{



    public static function getInstance(array $datas): self
    {
        $instance = new self();
        $instance->init($datas);
        return $instance;
    }

    public function init(array $datas): void
    {
    }
}