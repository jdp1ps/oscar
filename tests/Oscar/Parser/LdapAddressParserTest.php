<?php

namespace Oscar\Connector\Parser;

use PHPUnit\Framework\TestCase;

class LdapAddressParserTest extends TestCase
{

    private LdapAddressParser $parser;
    
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
        $address = 'Centre Meudon$1 PLACE ARISTIDE BRIAND$MEUDON$France';
        // expect exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid address');
        $this->parser->parse($address);
    }

    public function testParseAddressWithInsufficientFields()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid address');

        $address = 'Centre Meudon$1 PLACE ARISTIDE BRIAND';
        $this->parser->parse($address);
    }

    public function testParseInternationalAddress()
    {
        $address = 'Some Institution$123 Example Street$12345 Example City$USA';
        $result = $this->parser->parse($address);

        $this->assertEquals('Some Institution', $result->street1);
        $this->assertEquals('123 Example Street', $result->street2);
        $this->assertEquals('12345', $result->zipCode);
        $this->assertEquals('Example City', $result->city);
        $this->assertEquals('USA', $result->country);
    }

    public function testParseInternationalAddressCityZipFormat()
    {
        $address = 'Another Institution$456 Another St$CityName 54321$Germany';
        $result = $this->parser->parse($address);

        $this->assertEquals('Another Institution', $result->street1);
        $this->assertEquals('456 Another St', $result->street2);
        $this->assertEquals('54321', $result->zipCode);
        $this->assertEquals('CityName', $result->city);
        $this->assertEquals('Germany', $result->country);
    }
}


