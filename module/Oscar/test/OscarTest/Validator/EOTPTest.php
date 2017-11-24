<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 20/10/15 10:17
 * @copyright Certic (c) 2015
 */
class EOTPTest extends \PHPUnit\Framework\TestCase
{

    public function testValid()
    {
        $validator = new \Oscar\Validator\EOTP();

        $handle = fopen(__DIR__.'/EOTPdatas.txt', "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $this->assertTrue($validator->isValid($line));
            }
            fclose($handle);
        } else {
            $this->fail('Impossible de lire le fichier contenant les codes EOTP valident');
        }
    }
}
