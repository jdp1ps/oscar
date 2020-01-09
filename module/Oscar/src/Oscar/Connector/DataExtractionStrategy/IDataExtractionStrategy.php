<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/20
 * Time: 13:33
 */

namespace Oscar\Connector\DataExtractionStrategy;


interface IDataExtractionStrategy
{
    public function extract($from);
}