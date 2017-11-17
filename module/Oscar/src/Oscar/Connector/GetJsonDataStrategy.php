<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-16 17:42
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;


abstract class GetJsonDataStrategy
{
    /**
     * @param $id
     * @return mixed stdObject
     */
    abstract public function getOne($id);
    abstract public function getAll();

    protected function stringToJson( $string ){
        $json = json_decode($string);
        if ($json === null && $string != '') {
            throw new NotJsonFileException("Le contenu source n'est pas du JSON valide.");
        }
        return $json;
    }

    /**
     * @return \stdClass[]
     *
    public function getAll()
    {
        static $datas;
        if ($datas === null) {
            $datas = $this->getJsonContent();
        }

        return $datas;
    }*/
}