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

        $re = '/([\w\-]*)( |\.)([\w\- \']*)(<(.*@.*)>)?/mu';

        if( preg_match($re, $data, $matches) ){

            $firstname = $matches[1];
            $lastname = trim($matches[3]);
            $email = count($matches) == 6 ? $matches[5] : "";
            $fullname = $firstname.' '.$lastname;

            return [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'fullname' => $fullname,
                'email' => $email
            ];
        }
        return null;
    }
}