<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 04/12/15 11:13
 * @copyright Certic (c) 2015
 */

namespace Oscar\Utils;


class FileMimeExtension
{
    const NATURE_DOCUMENT   = 'document';
    const NATURE_IMAGE      = 'image';
    const NATURE_ARCHIVE    = 'archive';
    const NATURE_EXECUTABLE = 'executable';
    const NATURE_SOURCE     = 'code source';

    private $_extensions = [];

    /**
     * @param $mime Le type mime
     * @param $ext L'extension de fichier attendue
     * @param string $nature
     * @param string $description
     * @return $this
     */
    public function loadExtension( $mime, $ext, $nature='', $description = '' )
    {
        $this->_extensions[$mime] = [
            'extension'     => $ext,
            'nature'        => $nature,
            'description'   => $description,
        ];
        return $this;
    }


    public function getExtensions()
    {
        return $this->_extensions;
    }
    public function getExtension( $mime )
    {

    }
}