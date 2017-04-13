<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 06/10/15 11:09
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;

use Oscar\Utils\FilesizeFormatter;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Mise en forme des dates (\DateTime).
 *
 * @package Oscar\View\Helpers
 */
class Filesize extends AbstractHtmlElement
{

    /**
     * @param integer $size
     * @return string
     */
    public function format( $size )
    {
        static $formatter;
        if( $formatter == null ){
            $formatter = new FilesizeFormatter();
        }
        return $formatter->format($size);
    }

    /**
     * @param integer $size
     * @return string
     */
    public function __invoke($size)
    {
        return $this->format($size);
    }
}
