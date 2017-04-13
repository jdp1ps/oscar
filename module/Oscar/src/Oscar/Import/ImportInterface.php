<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 22/01/16 10:41
 * @copyright Certic (c) 2016
 */

namespace Oscar\Import;

interface ImportInterface {
    function importAll( $options = null );
    function importOne( $object, $options = null);
}