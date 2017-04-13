<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/10/15 10:58
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;


use Zend\View\Helper\AbstractHtmlElement;

class Slugify extends AbstractHtmlElement
{
    function __invoke( $data = null )
    {
        static $sluger;
        if( $sluger === null ){
            $sluger = new \Cocur\Slugify\Slugify();
        }

        if( $data ){
            return $sluger->slugify((string) $data);
        } else {
            return '';
        }
    }
}