<?php
namespace UnicaenApp\Test\Constraint;

use PHPUnit_Framework_Constraint;
use Exception;

/**
 * Description of ZipValid
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class ZipFileValid extends PHPUnit_Framework_Constraint
{
    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     */
    protected function matches($other)
    {
        $dir = sys_get_temp_dir() . '/' . uniqid();
        
        $z = new \ZipArchive();
        $z->open($other);
        try {
            $result = $z->extractTo($dir);
        }
        catch (Exception $exc) {
            return false;
        }

        if (file_exists($dir)) {
            \UnicaenApp\Util::removeFile($dir);
        }
        
        return $result && "No error" === $z->getStatusString();
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param  mixed $other Evaluated value or object.
     * @return string
     */
    protected function failureDescription($other)
    {
        return sprintf(
          'zip file "%s" is valid',
          $other
        );
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return 'zip file is valid';
    }
}