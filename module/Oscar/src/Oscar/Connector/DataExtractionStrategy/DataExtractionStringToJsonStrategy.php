<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/01/20
 * Time: 13:34
 */

namespace Oscar\Connector\DataExtractionStrategy;


use Oscar\Utils\PhpPolyfill;

class DataExtractionStringToJsonStrategy implements IDataExtractionStrategy
{
    public function extract($from)
    {
        return PhpPolyfill::jsonDecode($from);
    }
}