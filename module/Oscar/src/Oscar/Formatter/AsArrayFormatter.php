<?php

namespace Oscar\Formatter;

interface AsArrayFormatter {

    /** Un tableau de valeur */
    const ARRAY_FLAT = "FORMAT_ARRAY_FLAT";

    /** Un tableau de clef => valeur */
    const ARRAY_KEY_VALUE = "FORMAT_KEY_VALUE";

    public function asArray( $format = self::ARRAY_FLAT );

}