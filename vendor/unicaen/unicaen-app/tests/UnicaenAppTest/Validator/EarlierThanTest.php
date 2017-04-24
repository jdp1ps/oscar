<?php
namespace UnicaenAppTest\Validator;

use UnicaenApp\Validator\EarlierThan;
use \DateTime;
use \DateInterval;

/**
 * @category   Unicaen
 * @package    Unicaen_Validator
 * @subpackage UnitTests
 * @group      Unicaen_Validator
 */
class EarlierThanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * 
     */
    public function testConstructWithFormatOption()
    {
        $traversable = new \ArrayObject(array('max' => new DateTime, 'format' => 'd/m/Y'));
        $validator = new EarlierThan($traversable);
        $this->assertEquals('d/m/Y', $validator->getFormat());
        $this->assertArrayNotHasKey('format', $validator->getOptions());
    }
    
    public function provideBasicDataset()
    {
        $date = new DateTime();
        
        $datePlusOneSecond = clone $date;
        $datePlusOneSecond->add(new DateInterval('PT1S')/* 1sec */);
        
        $datePlusOneDay = clone $date;
        $datePlusOneDay->add(new DateInterval('P1D')/* 1j */);
        
        $dateMinusOneSecond = clone $date;
        $dateMinusOneSecond->sub(new DateInterval('PT1S')/* 1sec */);
        
        $dateMinusOneDay = clone $date;
        $dateMinusOneDay->sub(new DateInterval('P1D')/* 1j */);
        
        return array(
            '-1s' => array(
                $date,  
                $dateMinusOneSecond, 
                true),
            '-1s-ignore-time' => array(
                array('max' => $date, 'ignore_time' => true),  
                $dateMinusOneSecond, 
                false),
            '-1d' => array(
                $date, 
                $dateMinusOneDay, 
                true),
            'same-not-inclusive' => array(
                $date, 
                clone $date, 
                false),
            '+1s' => array(
                $date, 
                $datePlusOneSecond, 
                false),
            'same-inclusive' => array(
                array('max' => $date, 'inclusive' => true),  
                clone $date, 
                true),
            '-1s-inclusive-ignore-time' => array(
                array('max' => $date, 'inclusive' => true, 'ignore_time' => true),  
                $dateMinusOneSecond, 
                true),
            '-1s-inclusive' => array(
                array('max' => $date, 'inclusive' => true),  
                $dateMinusOneSecond, 
                true),
            '+1s-inclusive' => array(
                array('max' => $date, 'inclusive' => true),  
                $datePlusOneSecond, 
                false),
            '+1d-inclusive' => array(
                array('max' => $date, 'inclusive' => true),  
                $datePlusOneDay, 
                false),
        );
    }
    
    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider provideBasicDataset
     */
    public function testBasic($testedDate, $dateToTestAgainst, $expected)
    {
        $validator = new EarlierThan($testedDate);
        
        $this->assertEquals($expected, $validator->isValid($dateToTestAgainst));
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $validator = new EarlierThan(new DateTime());
        $this->assertEquals(array(), $validator->getMessages());
    }

    /**
     * Ensures that setMax() accepts string value
     *
     * @return void
     */
    public function testSetMaxAsString()
    {
        $format = 'd/m/Y';
        $validator = new EarlierThan(array('max' => $minString = '25/12/2012', 'format' => $format));
        $this->assertInstanceOf('\DateTime', $validator->getMax());
        $this->assertEquals($minString, $validator->getMax()->format($format));
        
        $validator->setMax($minString = '01/01/2013');
        $this->assertEquals($minString, $validator->getMax()->format($format));
    }

    /**
     * Ensures that __construct() throws exception if max option is specified in bad format
     *
     * @return void
     * @expectedException \Zend\Validator\Exception\InvalidArgumentException
     */
    public function testConstructWithMaxAsStringWithBadFormat()
    {
        $format = 'd/m/Y';
        new EarlierThan(array('max' => $maxString = '25/12', 'format' => $format));
    }

    /**
     * Ensures that setMax() throws exception if max option is specified in bad format
     *
     * @return void
     * @expectedException \Zend\Validator\Exception\InvalidArgumentException
     */
    public function testSetMaxAsStringWithBadFormat()
    {
        $format = 'd/m/Y';
        $validator = new EarlierThan(array('max' => $maxString = '25/12/2012', 'format' => $format));
        $validator->setMax($maxString = '01/01');
    }

    /**
     * Ensures that getMax() returns expected value
     *
     * @return void
     */
    public function testGetMax()
    {
        $validator = new EarlierThan($date = new DateTime());
        $this->assertEquals($date, $validator->getMax());
    }

    /**
     * Ensures that setValue() accepts string value
     *
     * @return void
     */
    public function testSetValueAsString()
    {
        $format = 'd/m/Y';
        $validator = new EarlierThan(array('max' => $maxString = '01/01/2013', 'format' => $format));
        $valid = $validator->isValid($dateString = '25/12/2012');
        $this->assertTrue($valid);
    }

    /**
     * Ensures that setValue() throws exception if value is specified in bad format
     *
     * @return void
     * @expectedException \Zend\Validator\Exception\InvalidArgumentException
     */
    public function testSetValueAsStringWithBadFormat()
    {
        $format = 'd/m/Y';
        $validator = new EarlierThan(array('max' => $maxString = '25/12/2012', 'format' => $format));
        $validator->isValid($dateString = '25/12');
    }

    /**
     * Ensures that getInclusive() returns expected default value
     *
     * @return void
     */
    public function testGetInclusive()
    {
        $validator = new EarlierThan(new DateTime());
        $this->assertEquals(false, $validator->getInclusive());
    }

    public function testEqualsMessageTemplates()
    {
        $validator = new EarlierThan(new DateTime());
        $this->assertAttributeEquals($validator->getOption('messageTemplates'),
                                     'messageTemplates', $validator);
    }

    public function testEqualsMessageVariables()
    {
        $validator = new EarlierThan(new DateTime());
        $this->assertAttributeEquals($validator->getOption('messageVariables'),
                                     'messageVariables', $validator);
    }
}
