<?php


namespace Oscar\Formatter;


class ResultFormatterString implements IResultFormatter
{
    public function format( array $result ):array
    {
        $out = [];
        foreach ($result as $object) {
            $out[] = $this->formatValue($object);
        }
        return $out;
    }

    public function formatValue( $object ) :string
    {
        return (string) $object;
    }
}