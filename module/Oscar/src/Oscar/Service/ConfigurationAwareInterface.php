<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/11/18
 * Time: 10:03
 */

namespace Oscar\Service;


interface ConfigurationAwareInterface
{
    /**
     * @return ConfigurationParser
     */
    public function getConfiguration($key);
}