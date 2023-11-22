<?php

namespace Oscar\Formatter\Timesheet;

use Oscar\Formatter\Output\OutputHtmlStrategy;
use Oscar\Formatter\Output\OutputWkhtmltopdfStrategy;

class TimesheetPersonPeriodPdfFormatter extends TimesheetPersonPeriodHtmlFormatter
{
    public function output(array $datas):void {
        $filename = $datas['filename'].'.pdf';
        $html = $this->render($datas);
        $output = new OutputWkhtmltopdfStrategy();
        $output->output($html, $filename, OutputWkhtmltopdfStrategy::ORIENTATION_LANDSCAPE);
    }
}