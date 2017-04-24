<?php
namespace UnicaenAppTest\Validator;

use UnicaenApp\Validator\LaterThan;
use \DateTime;
use \DateInterval;

/**
 * @category   Unicaen
 * @package    Unicaen_Validator
 * @subpackage UnitTests
 * @group      Unicaen_Validator
 */
class LaterThanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * 
     */
    public function testConstructWithFormatOption()
    {
        $traversable = new \ArrayObject(array('min' => new DateTime, 'format' => 'd/m/Y'));
        $validator = new LaterThan($traversable);
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
                false),
            '-1s-ignore-time' => array(
                array('min' => $date, 'ignore_time' => true),  
                $dateMinusOneSecond, 
                false),
            '-1d' => array(
                $date, 
                $dateMinusOneDay, 
                false),
            'same-not-inclusive' => array(
                $date, 
                clone $date, 
                false),
            '+1s' => array(
                $date, 
                $datePlusOneSecond, 
                true),
            'same-inclusive' => array(
                array('min' => $date, 'inclusive' => true),  
                clone $date, 
                true),
            '+1s-inclusive-ignore-time' => array(
                array('min' => $date, 'inclusive' => true, 'ignore_time' => true),  
                $datePlusOneSecond, 
                true),
            '-1s-inclusive' => array(
                array('min' => $date, 'inclusive' => true),  
                $dateMinusOneSecond, 
                false),
            '-1d-inclusive' => array(
                array('min' => $date, 'inclusive' => true),  
                $dateMinusOneDay, 
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
        $validator = new LaterThan($testedDate);
        
        $this->assertEquals($expected, $validator->isValid($dateToTestAgainst));
    }

    /**
     * Ensures that getMessages() returns expected default value
     *
     * @return void
     */
    public function testGetMessages()
    {
        $validator = new LaterThan(new DateTime());
        $this->assertEquals(array(), $validator->getMessages());
    }

    /**
     * Ensures that setMin() accepts string value
     *
     * @return void
     */
    public function testSetMinAsString()
    {
        $format = 'd/m/Y';
        $validator = new LaterThan(array('min' => $minString = '25/12/2012', 'format' => $format));
        $this->assertInstanceOf('\DateTime', $validator->getMin());
        $this->assertEquals($minString, $validator->getMin()->format($format));
        
        $validator->setMin($minString = '01/01/2013');
        $this->assertEquals($minString, $validator->getMin()->format($format));
    }

    /**
     * Ensures that __construct() throws exception if min option is specified in bad format
     *
     * @return void
     * @expectedException \Zend\Validator\Exception\InvalidArgumentException
     */
    public function testConstructWithMinAsStringWithBadFormat()
    {
        $format = 'd/m/Y';
        new LaterThan(array('min' => $minString = '25/12', 'format' => $format));
    }

    /**
     * Ensures that setMin() throws exception if min option is specified in bad format
     *
     * @return void
     * @expectedException \Zend\Validator\Exception\InvalidArgumentException
     */
    public function testSetMinAsStringWithBadFormat()
    {
        $format = 'd/m/Y';
        $validator = new LaterThan(array('min' => $minString = '25/12/2012', 'format' => $format));
        $validator->setMin($minString = '01/01');
    }

    /**
     * Ensures that getMin() returns expected value
     *
     * @return void
     */
    public function testGetMin()
    {
        $validator = new LaterThan($date = new DateTime());
        $this->assertEquals($date, $validator->getMin());
    }

    /**
     * Ensures that setValue() accepts string value
     *
     * @return void
     */
    public function testSetValueAsString()
    {
        $format = 'd/m/Y';
        $validator = new LaterThan(array('min' => $minString = '01/01/2013', 'format' => $format));
        $valid = $validator->isValid($dateString = '25/12/2013');
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
        $validator = new LaterThan(array('min' => $minString = '25/12/2012', 'format' => $format));
        $validator->isValid($dateString = '25/12');
    }

    /**
     * Ensures that getInclusive() returns expected default value
     *
     * @return void
     */
    public function testGetInclusive()
    {
        $validator = new LaterThan(new DateTime());
        $this->assertEquals(false, $validator->getInclusive());
    }

    public function testEqualsMessageTemplates()
    {
        $validator = new LaterThan(new DateTime());
        $this->assertAttributeEquals($validator->getOption('messageTemplates'),
                                     'messageTemplates', $validator);
    }

    public function testEqualsMessageVariables()
    {
        $validator = new LaterThan(new DateTime());
        $this->assertAttributeEquals($validator->getOption('messageVariables'),
                                     'messageVariables', $validator);
    }
}
