<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 26/10/15 11:36
 * @copyright Certic (c) 2015
 */

namespace Oscar\Utils;


class FilesizeFormatter
{

    private $units = ['Octet(s)', 'Ko', 'Mo', 'Go', 'To'];

    public function format( $size ){
        $unit = 0;
        while( $unit < count($this->units)-1 && $size / 1024 >= 1 ){
            $unit++;
            $size /= 1024;
        }
        if( !is_int($size) ){
            $size = number_format($size,1,',',' ');
        }
        return $size.' ' . $this->units[$unit];
    }
}