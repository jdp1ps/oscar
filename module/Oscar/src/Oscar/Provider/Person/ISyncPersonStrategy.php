<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 11/03/16
 * Time: 16:48
 */

namespace Oscar\Provider\Person;


use Oscar\Entity\Person;

interface ISyncPersonStrategy
{
    public function sync(Person $person);
}