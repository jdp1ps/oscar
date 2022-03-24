<?php


namespace Oscar\Formatter;


interface OscarFormatterConst
{
    // Liste de valeurs (Chaîne simple)
    // [
    //  "Valeur A",
    //  "Valeur B"
    //  ]
    const FORMAT_ARRAY_FLAT = 'format_array_flat';

    // Liste de valeur avec l'identifiant pour clef
    // [
    //  "IDA" => "Valeur A",
    //  "IDB" => "Valeur B"
    //  ]
    const FORMAT_ARRAY_ID_VALUE = 'format_array_id_value';

    // Liste d'objet
    // [
    //  ["id" => "IDA", "label" => "Valeur A"],
    //  ["id" => "IDB", "label" => "Valeur B"]
    // ]
    const FORMAT_ARRAY_OBJECT = 'format_array_object';

    // Liste d'objet avec l'id pour clef
    // [
    //  "IDA" => ["id" => "IDA", "label" => "Valeur A"],
    //  "IDB" => ["id" => "IDB", "label" => "Valeur B"]
    // ]
    const FORMAT_ARRAY_ID_OBJECT = 'format_array_id_object';

    //
    const FORMAT_IO_CSV = 'csv';

    const FORMAT_IO_JSON = 'json';

    const FORMAT_IO_XML = 'xml';
}