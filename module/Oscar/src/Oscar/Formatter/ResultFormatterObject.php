<?php


namespace Oscar\Formatter;


class ResultFormatterObject implements IResultFormatter
{
    public function format( array $result ):array
    {
        return $result;
    }
}