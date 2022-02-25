<?php


namespace Oscar\Formatter;


class ResultFormatterIdString implements IResultFormatter
{
    public function format( array $result ):array
    {
        $out = [];
        foreach ($result as $object) {
            $out[$this->formatKey($object)] = $this->formatValue($object);
        }
        return $out;
    }

    public function formatKey( $object )
    {
        return $object->getId();
    }

    public function formatValue( $object ) :string
    {
        return (string) $object;
    }
}