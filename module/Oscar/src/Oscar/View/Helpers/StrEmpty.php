<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 12/10/15 10:58
 * @copyright Certic (c) 2015
 */

namespace Oscar\View\Helpers;


use Laminas\View\Helper\AbstractHtmlElement;

class StrEmpty extends AbstractHtmlElement
{
    function __invoke( $data = null, $ifNull='Aucune donnée')
    {
        if( $data ){
            return (string) $data;
        } else {
            return '<span class="no-data">'.$ifNull.'</span>';
        }
    }
}