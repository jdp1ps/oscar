<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 07/12/2017
 * Time: 10:34
 */

namespace Oscar\Import\Data;


class DataExtractorOrganization extends AbstractDataExtractor
{
    function extract($data, $params = null)
    {
        if( preg_match("/(\[(\w*)\])? ?([A-Z]* )?(.*)/", $data, $matches) ){
            $code = $matches[2];
            $short = $matches[3];
            $long = $matches[4];
            return [
                'code' => trim($code),
                'shortname' => trim($short),
                'longname' => trim($long),
            ];
        }
        return $data;
    }

}