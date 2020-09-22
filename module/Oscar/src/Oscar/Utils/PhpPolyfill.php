<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 28/02/19
 * Time: 14:10
 */

namespace Oscar\Utils;


class PhpPolyfill
{
    static function jsonErrors()
    {
        static $ERRORS;
        if ($ERRORS === null) {
            $ERRORS = array(
                JSON_ERROR_NONE => 'No error',
                JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
                JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
                JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
                JSON_ERROR_SYNTAX => 'Syntax error',
                JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
            );
        }
        return $ERRORS;
    }

    static function jsonErrorMsg($code){
        $errors = self::jsonErrors();
        if( array_key_exists($code, $errors) ){
            return $errors[$code];
        }
        return "Unknow Error !";
    }

    /**
     * @param $value
     * @param int $option
     * @param int $depth
     * @return false|string
     * @throws \Exception
     */
    public static function jsonEncode($value, $option = 0, $depth = 512)
    {
        $json = json_encode($value, $option, $depth);
        $error = json_last_error();

        if ($error === JSON_ERROR_NONE) {
            return $json;
        } else {
            throw new \Exception("Can't encode data to JSON : " . self::jsonErrorMsg($error) . '('.  substr($value, 0, 100).')');
        }
    }

    /**
     * @param $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     * @throws \Exception
     */
    public static function jsonDecode($json, $assoc = false, $depth = 512, $options = 0)
    {
        $result = json_decode($json, $assoc, $depth, $options);
        $error = json_last_error();

        if ($error === JSON_ERROR_NONE) {
            return $result;
        } else {
            throw new \Exception("Can't decode data to JSON : " . self::jsonErrorMsg($error));
        }
    }
}