<?php

namespace Oscar\Formatter\Timesheet;

use Oscar\Formatter\Output\OutputWkhtmltopdfStrategy;

class TimesheetPeriodPdfFormatter extends TimesheetPeriodHtmlFormatter
{
    public function output(array $datas): void
    {
        $filename = 'Repport-' . $datas['activity']['num'] . '.pdf';
        $html = $this->render($datas);
        $output = new OutputWkhtmltopdfStrategy();
        $output->output($html, $filename, OutputWkhtmltopdfStrategy::ORIENTATION_LANDSCAPE);
    }
}