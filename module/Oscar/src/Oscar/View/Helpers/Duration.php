<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 06/10/15 11:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;

use Zend\View\Helper\AbstractHtmlElement;

/**
 * Mise en forme des dates (\DateTime).
 *
 * @package Oscar\View\Helpers
 */
class Duration extends AbstractHtmlElement
{
    public function format($duration)
    {
        $heures = floor($duration);
        $minutes = round(($duration - $heures)*60);
        if( $minutes < 10 ){
            $minutes = '0'.$minutes;
        }
        return sprintf('%s:%s', $heures, $minutes);
    }

    /**
     * @param $duration float
     * @return string
     */
    public function __invoke($duration)
    {
        return $this->format($duration);
    }
}
