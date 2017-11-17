<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-16 17:57
 * @copyright Certic (c) 2017
 */

namespace Oscar\Connector;


use Oscar\Exception\ConnectorException;
use Oscar\Exception\OscarException;

class GetJsonDataFromFileStrategy implements GetJsonDataStrategy
{
    private $filepath;

    /**
     * GetJsonDataFromFileStrategy constructor.
     *
     * @param $filepath
     */
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * Retourne l'emplacement du fichier.
     *
     * @return mixed
     */
    public function getFilepath()
    {
        if (!file_exists($this->filepath)) {
            throw new OscarException(sprintf("Le fichier '%s' n'existe pas",
                $this->filepath));
        }

        return $this->filepath;
    }

    /**
     * Retourne le contenu du fichier.
     *
     * @return bool|string
     * @throws OscarException
     */
    public function getFileContent()
    {
        $file = $this->getFilepath();
        if (!is_readable($file)) {
            throw new OscarException(sprintf("Impossible de lire le fichier '%s'.",
                $file));
        }
        return file_get_contents($file);
    }

    public function getJsonContent()
    {
        $content = $this->getFileContent();
        $json = json_decode($content);
        if( $json === null && $content != '' ){
            throw new NotJsonFileException(sprintf("Le fichier '%s' n'est pas un fichier JSON valide.", $this->filepath));
        }
        return $json;
    }

    public function getAll()
    {
        static $datas;
        if ($datas === null) {
            $datas = $this->getJsonContent();
        }

        return $datas;
    }


    public function getOne($id)
    {
        foreach ($this->getAll() as $entry) {
            if ($entry->uid == $id) {
                return $entry;
            }
        }
        return null;
    }
}