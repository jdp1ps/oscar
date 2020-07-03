<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 08/07/2016
 * Time: 10:51
 */

namespace Oscar\Formatter;


class AbstractCSVFormatter
{
    /**
     * Met en forme une date.
     *
     * @param $data
     * @param string $format
     * @return string
     */
    public function formatDate( $data, $format="Y-m-d" ){
        if( $data instanceof \DateTime ){
            return $data->format($format);
        }
        return "";
    }

    /**
     * Met en forme de l'oseille.
     *
     * @param $data
     * @return string
     */
    public function formatMoney( $data ){
        if( is_double($data) ){
            return number_format($data, 2, ',', '');
        }
        return '';
    }
}