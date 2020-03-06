<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 12/02/20
 * Time: 12:23
 */

namespace Oscar\Import\Data;


use Oscar\Exception\OscarException;

class DataStringArray extends AbstractDataExtractor
{
    const OPTION_TRIM_INDICES = 'option_trim_indices';
    const OPTION_UNIQUE_VALUE = 'option_unique_value';

    /**
     * DataStringArray constructor.
     */
    public function __construct()
    {
        $this->setOption(self::OPTION_THROW, true);
        $this->setOption(self::OPTION_ALLOW_EMPTY, true);
        $this->setOption(self::OPTION_TRIM_INDICES, true);
    }

    function extract($data, $params = null)
    {

        if( $params != null ) {
            $this->configure($params);
        }

        if( !is_string($data) ){
            $error = _("Type de donnée inattendue");
            $this->setError($error);
        }

        $input = $this->purgeInput($data);

        if( $input == self::EMPTY_STRING ){
            $array = [];
        } else {
            $array = [];
            foreach( explode(',', $input) as $value ){
                if( $this->getOption(self::OPTION_TRIM_INDICES, true) ){
                    $value = trim($value);
                }
                $array[] = $value;
            }
        }

        if( $this->getOption(self::OPTION_ALLOW_EMPTY) === false && count($array) == 0 ){
            $this->setOption(_('La donnée ne peut pas être vide'));
        }

        if( $this->getOption(self::OPTION_UNIQUE_VALUE, true) ){
            $array = array_unique($array);
        }

        return $array;
    }
}