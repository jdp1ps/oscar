<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 28/09/15 16:23
 *
 * @copyright Certic (c) 2015
 */
namespace Oscar\View\Helpers;

use Laminas\View\Helper\AbstractHtmlElement;

class DateRenderer extends AbstractHtmlElement
{
    public function __invoke($date)
    {
        return $this->render($date);
    }

    public function render($date, $format = 'M Y')
    {
        if (!$date) {
            return '';
        } else {
            if ($date instanceof \DateTime) {
                return $date->format('M Y');
            }
        }

        return 'BUG';
    }
}
