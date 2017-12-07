<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 07/12/2017
 * Time: 10:08
 */

namespace Oscar\Import\Data;

/**
 * Cette classe permet d'extraire les noms et prénoms depuis une chaîne de caractère.
 *
 * @package Oscar\Import\Data
 */
class DataExtractorFullname extends AbstractDataExtractor
{
    function extract($data, $params = null)
    {
        if( !is_string($data) )
            return null;

        if( preg_match('/(.*)( |\.)(.*)/i', $data, $matches) ){
            return [
                'firstname' => $matches[1],
                'lastname' => $matches[3],
            ];
        }
        return null;
    }
}