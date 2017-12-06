<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-12-06 10:02
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Data;


interface IDataExtractor
{
    function extract( $data, $params = null );
    function hasError();
    function getError();
}