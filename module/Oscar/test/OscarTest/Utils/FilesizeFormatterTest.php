<?php

/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 20/10/15 10:17
 * @copyright Certic (c) 2015
 */
class FilesizeFormatterTest extends \PHPUnit\Framework\TestCase
{

    public function testFormat()
    {
        $formatter = new \Oscar\Utils\FilesizeFormatter();


        $this->assertEquals($formatter->format(512), '512 Octet(s)');
        $this->assertEquals($formatter->format(1024), '1 Ko');
        $this->assertEquals($formatter->format(1024*1024), '1 Mo');
        $this->assertEquals($formatter->format(1024*1024*1024), '1 Go');
        $this->assertEquals($formatter->format(1024*1024*1024*1024), '1 To');
        $this->assertEquals($formatter->format(1024*1024*1024*1024*10), '10 To');
        $this->assertEquals($formatter->format(1024*1024*1024*1024*25), '25 To');

        $this->assertEquals($formatter->format(1024*1024*1024*1024*1024), '1024 To');

        // Deciamal
        $this->assertEquals($formatter->format(1024*1024*3.5), '3,5 Mo');
        $this->assertEquals($formatter->format(1024*1024*3.553), '3,6 Mo');
        $this->assertEquals($formatter->format(1024*1024*3.9999), '4,0 Mo');

    }
}
