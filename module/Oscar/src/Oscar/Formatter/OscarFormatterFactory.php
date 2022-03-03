<?php


namespace Oscar\Formatter;


use Oscar\Exception\OscarException;

class OscarFormatterFactory implements OscarFormatterConst
{
    /**
     * @param string $formatterName
     * @return IResultFormatter
     * @throws OscarException
     */
    public static function getFormatter(string $formatterName): IResultFormatter
    {
        if ($formatterName == self::FORMAT_ARRAY_ID_VALUE) {
            return new ResultFormatterIdString();
        } elseif ($formatterName == self::FORMAT_ARRAY_ID_OBJECT) {
            return new ResultFormatterIdObject();
        } elseif ($formatterName == self::FORMAT_ARRAY_FLAT) {
            return new ResultFormatterString();
        } elseif ($formatterName == self::FORMAT_ARRAY_OBJECT) {
            return new ResultFormatterObject();
        }

        throw new OscarException(sprintf("Formateur de résultat '%s' non trouvé", $formatterName));
    }
}