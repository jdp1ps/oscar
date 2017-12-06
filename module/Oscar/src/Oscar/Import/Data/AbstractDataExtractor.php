<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-12-06 10:16
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Data;


abstract class AbstractDataExtractor implements IDataExtractor
{
    private $error = "";

    function hasError()
    {
        return $this->error != "";
    }

    function getError()
    {
        return $this->error;
    }

    protected function setError( $errorMessage ){
        $this->error = $errorMessage;
    }

}