<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 05/09/18
 * Time: 16:07
 */

namespace Oscar\Utils;


class ConfigurationMergable
{
    private $initaleDatas;

    public function __construct( $initaleDatas )
    {
        $this->init($initaleDatas);
    }

    public function init( $initaleDatas ){
        $this->initaleDatas = $initaleDatas;
    }

    public function merge( $datas ){
        if( $datas ){

        }
    }

    public function get($key){
        return $this->extract($key);
    }

    public function getValue($key){
        $data = $this->extract($key);
        if( !array_key_exists('value', $data) ){
            throw new \Exception("Erreur, il faut une clef 'value' dans la configuration");
        }
        return $data['value'];
    }

    public function set($key, $value){

    }

    protected function extract( $key ){
        $keys = explode('.', $key);
        $datas = $this->initaleDatas;

        foreach ($keys as $keyDeep) {
            if( !array_key_exists($keyDeep, $datas) ){
                throw new \Exception(sprintf("La configuration '%s' n'existe pas.", $keyDeep));
            }
            $datas = $datas[$keyDeep];
        }
        return $datas;
    }
}