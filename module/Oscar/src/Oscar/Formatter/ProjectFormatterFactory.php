<?php


namespace Oscar\Formatter;


use Oscar\Exception\OscarException;

class ProjectFormatterFactory
{
    public static function getFormatter( string $name ) :IProjectFormater
    {
        switch ($name) {
            case OscarFormatterConst::FORMAT_IO_CSV:
                return new ProjectToArrayFormatter();

        }
        throw new OscarException(_("Formatteur de projet inconnu"));
    }
}