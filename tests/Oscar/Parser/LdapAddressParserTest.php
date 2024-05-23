<?php

namespace Oscar\Parser;

use Oscar\Connector\Parser\LdapAddressParser;
use PHPUnit\Framework\TestCase;

class LdapAddressParserTest extends TestCase
{
    protected function setUp(): void
    {
        $this->parser = new LdapAddressParser();
    }

    public function testParseAddressWithCountry()
    {
        $address = 'Centre Meudon$1 PLACE ARISTIDE BRIAND$92190 MEUDON$France';
        $result = $this->parser->parse($address);

        $this->assertEquals('Centre Meudon', $result->street1);
        $this->assertEquals('1 PLACE ARISTIDE BRIAND', $result->street2);
        $this->assertEquals('92190', $result->zipCode);
        $this->assertEquals('MEUDON', $result->city);
        $this->assertEquals('France', $result->country);
    }

    public function testParseAddressWithoutCountry()
    {
        $address = 'Centre Meudon$1 PLACE ARISTIDE BRIAND$92190 MEUDON';
        $result = $this->parser->parse($address);

        $this->assertEquals('Centre Meudon', $result->street1);
        $this->assertEquals('1 PLACE ARISTIDE BRIAND', $result->street2);
        $this->assertEquals('92190', $result->zipCode);
        $this->assertEquals('MEUDON', $result->city);
        $this->assertNull($result->country);
    }

    public function testParseAddressWithThreeStreetLines()
    {
        $address = 'Line1$Line2$Line3$75001 Paris$France';
        $result = $this->parser->parse($address);

        $this->assertEquals('Line1', $result->street1);
        $this->assertEquals('Line2', $result->street2);
        $this->assertEquals('Line3', $result->street3);
        $this->assertEquals('75001', $result->zipCode);
        $this->assertEquals('Paris', $result->city);
        $this->assertEquals('France', $result->country);
    }

    public function testParseAddressWithInvalidZipCode()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid zip code');

        $address = 'Centre Meudon$1 PLACE ARISTIDE BRIAND$MEUDON$France';
        $this->parser->parse($address);
    }

    public function testParseAddressWithInsufficientFields()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid zip code: 1');

        $address = 'Centre Meudon$1 PLACE ARISTIDE BRIAND';
        $this->parser->parse($address);
    }
}


