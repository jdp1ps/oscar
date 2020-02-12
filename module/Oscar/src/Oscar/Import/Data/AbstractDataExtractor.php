<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-12-06 10:16
 * @copyright Certic (c) 2017
 */

namespace Oscar\Import\Data;


use Oscar\Exception\OscarException;

abstract class AbstractDataExtractor implements IDataExtractor
{
    private $error = "";
    private $options = null;

    const OPTION_THROW = "option_throw";
    const OPTION_ALLOW_EMPTY = "option_allow_empty";
    const OPTION_TRIM_INPUT = "option_trim_input";

    const EMPTY_STRING = "";

    /**
     * AbstractDataExtractor constructor.
     */
    public function __construct()
    {
        $this->options = [];
    }

    public function purgeInput( string $input ) :string{
        $output = $input;
        if( $this->getOption(self::OPTION_TRIM_INPUT, true) ){
            $output = trim($input);
        }
        return $output;
    }


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
        if( $this->getOption(self::OPTION_THROW, false) ){
            throw new OscarException($errorMessage);
        }
    }

    public function setOption( $optionName, $optionValue ){
        $this->options[$optionName] = $optionValue;
    }

    public function getOption($optionName, $default = null){
        if( array_key_exists($optionName, $this->options) ){
            return $this->options[$optionName];
        }
        return $default;
    }

    public function configure( array $params ){
        foreach ($params as $key=>$value) {
            $this->setOption($key, $value);
        }
    }
}