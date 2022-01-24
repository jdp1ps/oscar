<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 08/07/2016
 * Time: 10:13
 */
namespace Oscar\Formatter\person;

interface IPersonFormatter {
    public function format( $person, ?array $options=null ) :array;
}