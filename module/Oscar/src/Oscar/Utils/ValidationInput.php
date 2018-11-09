<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 09/11/18
 * Time: 12:44
 */

namespace Oscar\Utils;


class ValidationInput
{
    public static function frequency( $input ){
        $frequencies = explode(',', $input);
        $result = [];
        foreach ($frequencies as $freq) {
            if (preg_match('/^(Lun|Mar|Mer|Jeu|Ven|Sam|Dim)(2[0-4]|1[0-9]|[1-9])$/', $freq)) {
                $result[] = $freq;
            }
        }
        return $result;
    }
}