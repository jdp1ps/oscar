<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-16 17:57
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;


use Oscar\Exception\ConnectorException;

class GetJsonDataFromFileStrategy implements GetJsonDataStrategy
{
    private $filepath;

    /**
     * GetJsonDataFromFileStrategy constructor.
     * @param $filepath
     */
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * @return mixed
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    public function getAll()
    {
        static $datas;
        if( $datas === null ){
            $datas = json_decode(file_get_contents($this->getFilepath()));
        }
        return $datas;
    }


    public function getOne($id)
    {
        foreach ($this->getAll() as $entry ){
            if( $entry->uid == $id )
                return $entry;
        }
        throw new ConnectorException(sprintf("L'entrée %s n'est pas disponible", $id));
    }
}