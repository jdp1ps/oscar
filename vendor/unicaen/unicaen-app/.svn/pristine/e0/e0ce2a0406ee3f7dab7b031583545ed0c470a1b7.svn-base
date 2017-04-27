<?php
namespace UnicaenAppTest\Form\Element;

use DateTime;
use PHPUnit_Framework_TestCase;
use UnicaenApp\Form\Element\DateInfSup;

/**
 * Description of DateInfSupTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class DateInfSupTest extends PHPUnit_Framework_TestCase
{
    const DATETIME_SEP = ' à ';
    
    protected $element;
    protected $dateStrFormat   = 'd/m/Y';
    protected $dateStrTiFormat = 'd/m/Y à H:i';
    protected $dateStr         = '11/03/2013';
    protected $dateStrTi       = '11/03/2013 à 15:37';
    
    protected function setUp()
    {
        $this->element = new DateInfSup('elem');
        $this->element->setDateTimeSeparator(self::DATETIME_SEP);
    }
    
    public function testContructingInitializesProperties()
    {
        $this->assertEquals(new DateTime, $this->element->getDateInf());
        $this->assertTrue($this->element->getDateSupActivated());
        $this->assertNull($this->element->getDateSup());
        $this->assertFalse($this->element->getIncludeTime());
        $this->assertNotEmpty($this->element->getDateInfLabel());
        $this->assertNotEmpty($this->element->getDateSupLabel());
    }
    
    public function testContructingWithOptions()
    {
        $element = new DateInfSup('elem', array('include_time' => true, 'date_inf_label' => "Date inf", 'date_sup_label' => "Date sup"));
        $this->assertTrue($element->getIncludeTime());
        $this->assertEquals("Date inf", $element->getDateInfLabel());
        $this->assertEquals("Date sup", $element->getDateSupLabel());
    }
    
    public function testCanGetDefaultDatetimeSeparator()
    {
        $element = new DateInfSup('elem');
        $this->assertNotEmpty($element->getDateTimeSeparator());
    }
    
    public function testCanSetDateLabels()
    {
        $this->element->setDateInfLabel($labelInf = "Date minimum")
                      ->setDateSupLabel($labelSup = "Date maximum");
        $this->assertEquals($labelInf, $this->element->getDateInfLabel());
        $this->assertEquals($labelSup, $this->element->getDateSupLabel());
    }
    
    public function testGettingDatesFormattedToStringTimeIncludedOrNot()
    {   
        $this->element->setDateInf($this->dateStr)
                      ->setDateSup($this->dateStr);
        
        $f1 = '\d{1,2}/\d{1,2}/\d{2,4}';
        $f2 = $f1 . self::DATETIME_SEP . '\d{1,2}:\d{2}';
        
        $this->element->setIncludeTime(false);
        $this->assertRegExp("`$f1`", $this->element->getDateInfToString());
        $this->assertRegExp("`$f1`", $this->element->getDateSupToString());
        
        $this->element->setIncludeTime(true);
        $this->assertRegExp("`$f2`", $this->element->getDateInfToString());
        $this->assertRegExp("`$f2`", $this->element->getDateSupToString());
    }
    
    public function testSettingValidDatesInStringFormatNormalizesThem()
    {
        $obj   = DateTime::createFromFormat($this->dateStrFormat, $this->dateStr);     // sans heure
        $objTi = DateTime::createFromFormat($this->dateStrTiFormat, $this->dateStrTi); // avec heure
        
        $obj->setTime($obj->format("H"), $obj->format("i"), 0); // secondes = 0
        $objTi->setTime($objTi->format("H"), $objTi->format("i"), 0);
        
        $this->element->setIncludeTime(false);
        // date attendue sans heure, date fournie sans heure
        $this->assertEquals($obj, $this->element->setDateInf($this->dateStr)->getDateInf());
        $this->assertEquals($obj, $this->element->setDateInfMin($this->dateStr)->getDateInfMin());
        $this->assertEquals($obj, $this->element->setDateInfMax($this->dateStr)->getDateInfMax());
        $this->assertEquals($obj, $this->element->setDateSup($this->dateStr)->getDateSup());
        $this->assertEquals($obj, $this->element->setDateSupMin($this->dateStr)->getDateSupMin());
        $this->assertEquals($obj, $this->element->setDateSupMax($this->dateStr)->getDateSupMax());
        
        $this->element->setIncludeTime(true);
        // date attendue avec heure, date fournie avec heure
        $this->assertEquals($objTi, $this->element->setDateInf($this->dateStrTi)->getDateInf());
        $this->assertEquals($objTi, $this->element->setDateInfMin($this->dateStrTi)->getDateInfMin());
        $this->assertEquals($objTi, $this->element->setDateInfMax($this->dateStrTi)->getDateInfMax());
        $this->assertEquals($objTi, $this->element->setDateSup($this->dateStrTi)->getDateSup());
        $this->assertEquals($objTi, $this->element->setDateSupMin($this->dateStrTi)->getDateSupMin());
        $this->assertEquals($objTi, $this->element->setDateSupMax($this->dateStrTi)->getDateSupMax());
    }
    
    public function getInvalidDatesInStringFormat()
    {
        return array(
            // date attendue sans heure mais date fournie AVEC heure :
            array(false, 'setDateInf', $this->dateStrTi),
            array(false, 'setDateInfMin', $this->dateStrTi),
            array(false, 'setDateInfMax', $this->dateStrTi),
            array(false, 'setDateSup', $this->dateStrTi),
            array(false, 'setDateSupMin', $this->dateStrTi),
            array(false, 'setDateSupMax', $this->dateStrTi),
            // date attendue avec heure mais date fournie SANS heure :
            array(true, 'setDateInf', $this->dateStr),
            array(true, 'setDateInfMin', $this->dateStr),
            array(true, 'setDateInfMax', $this->dateStr),
            array(true, 'setDateSup', $this->dateStr),
            array(true, 'setDateSupMin', $this->dateStr),
            array(true, 'setDateSupMax', $this->dateStr),
        );
    }
    
    /**
     * @dataProvider getInvalidDatesInStringFormat
     * @expectedException \InvalidArgumentException
     */
    public function testSettingInvalidDatesInStringFormatThrowsFormatException($includeTime, $method, $dateStr)
    {
        $this->element->setIncludeTime($includeTime)
                      ->$method($dateStr);
    }
    
    /**
     * Vérifie que 2 dates sont "égales", c'est à dire qu'elles ne diffèrent que de quelques secondes.
     * 
     * Nécessaire car la classe testée est amenée à créer un objet DateTime à partir d'une string
     * n'incluant pas l'heure alors que le format attendu l'inclut : dans ce cas le moteur PHP
     * initialise l'heure du nouvel objet avec l'heure courante
     * 
     * @param DateTime $expected Date au format objet
     * @param DateTime $actual Date au format objet
     */
    protected function assertDateEquals(DateTime $expected, DateTime $actual)
    {
        $interval = $expected->diff($actual, true); /* @var $interval \DateInterval */
        // si les 2 dates ne diffèrent que de quelques secondes, elles sont considérées égales
        $this->assertEquals(
                array(0, 0, 0, 0, 0), 
                array($interval->y, $interval->m, $interval->d, $interval->h,  $interval->i));
    }
    
    public function getEmptyValue()
    {
        return array(
            array(false, null),
            array(false, ''),
            array(false, array()),
            array(false, array(null, null)),
            array(false, array('', '')),
            array(false, array('inf' => null, 'sup' => null)),
            array(true, null),
            array(true, ''),
            array(true, array()),
            array(true, array(null, null)),
            array(true, array('', '')),
            array(true, array('inf' => null, 'sup' => null)),
        );
    }
    
    /**
     * @dataProvider getEmptyValue
     * @param boolean $includeTime Utiliser le format incluant l'heure ?
     * @param mixed $value Valeur
     */
    public function testSettingEmptyValue($includeTime, $value)
    {
        $this->element->setIncludeTime($includeTime)
                      ->setDateInf(null)
                      ->setDateSup(null);
        
        $this->element->setValue($value);
        $this->assertNull($this->element->getDateInf());
        $this->assertNull($this->element->getDateSup());
        $this->assertEquals(array('inf' => null, 'sup' => null), $this->element->getValue());
        $this->assertEquals('', $this->element->getDateInfToString());
        $this->assertEquals('', $this->element->getDateSupToString());
    }
    
    public function getValueIncludingDateInfOnly()
    {
        $date   = DateTime::createFromFormat($this->dateStrFormat, $this->dateStr);
        $dateTi = DateTime::createFromFormat($this->dateStrTiFormat, $this->dateStrTi);
        return array(
            array(false, $date),
            array(false, array($date)),
            array(false, array('inf' => $date)),
            array(false, $this->dateStr),
            array(false, array($this->dateStr)),
            array(false, array('inf' => $this->dateStr)),
            array(true, $dateTi),
            array(true, array($dateTi)),
            array(true, array('inf' => $dateTi)),
            array(true, $this->dateStrTi),
            array(true, array($this->dateStrTi)),
            array(true, array('inf' => $this->dateStrTi)),
        );
    }
    
    /**
     * @dataProvider getValueIncludingDateInfOnly
     * @param boolean $includeTime Utiliser le format incluant l'heure ?
     * @param mixed $value Valeur
     */
    public function testSettingValueIncludingDateInfOnly($includeTime, $value)
    {
        $this->element->setIncludeTime($includeTime)
                      ->setDateInf(null)
                      ->setDateSup(null);
        
        $this->element->setValue($value);
        $expected = $includeTime ? $this->dateStrTi : $this->dateStr;
        $this->assertEquals($expected, $this->element->getDateInfToString());
        $this->assertNull($this->element->getDateSup());
        $this->assertEquals(array('inf' => $expected, 'sup' => null), $this->element->getValue());
        $this->assertEquals('', $this->element->getDateSupToString());
    }
    
    public function getValueIncludingDateSupOnly()
    {
        $date   = DateTime::createFromFormat($this->dateStrFormat, $this->dateStr);
        $dateTi = DateTime::createFromFormat($this->dateStrTiFormat, $this->dateStrTi);
        return array(
            array(false, array('sup' => $date)),
            array(false, array('sup' => $this->dateStr)),
            array(true, array('sup' => $dateTi)),
            array(true, array('sup' => $this->dateStrTi)),
        );
    }
    
    /**
     * @dataProvider getValueIncludingDateSupOnly
     * @param boolean $includeTime Utiliser le format incluant l'heure ?
     * @param mixed $value Valeur
     */
    public function testSettingValueIncludingDateSupOnly($includeTime, $value)
    {
        $this->element->setIncludeTime($includeTime)
                      ->setDateInf(null)
                      ->setDateSup(null);
        
        $this->element->setValue($value);
        $expected = $includeTime ? $this->dateStrTi : $this->dateStr;
        $this->assertNull($this->element->getDateInf());
        $this->assertEquals($expected, $this->element->getDateSupToString());
        $this->assertEquals(array('inf' => null, 'sup' => $expected), $this->element->getValue());
        $this->assertEquals('', $this->element->getDateInfToString());
    }
    
    public function getValueIncludingBothDates()
    {
        $date   = DateTime::createFromFormat($this->dateStrFormat, $this->dateStr);
        $dateTi = DateTime::createFromFormat($this->dateStrTiFormat, $this->dateStrTi);
        return array(
            array(false, array($date, $date)),
            array(false, array('inf' => $date, 'sup' => $date)),
            array(false, array($this->dateStr, $this->dateStr)),
            array(false, array('inf' => $this->dateStr, 'sup' => $this->dateStr)),
            array(true, array($dateTi, $dateTi)),
            array(true, array('inf' => $dateTi, 'sup' => $dateTi)),
            array(true, array($this->dateStrTi, $this->dateStrTi)),
            array(true, array('inf' => $this->dateStrTi, 'sup' => $this->dateStrTi)),
        );
    }
    
    /**
     * @dataProvider getValueIncludingBothDates
     * @param boolean $includeTime Utiliser le format incluant l'heure ?
     * @param mixed $value Valeur
     */
    public function testSettingValueIncludingBothDates($includeTime, $value)
    {
        $this->element->setIncludeTime($includeTime)
                      ->setDateInf(null)
                      ->setDateSup(null);
        
        $this->element->setValue($value);
        $expected = $includeTime ? $this->dateStrTi : $this->dateStr;
        $this->assertEquals($expected, $this->element->getDateInfToString());
        $this->assertEquals($expected, $this->element->getDateSupToString());
        $this->assertEquals(array('inf' => $expected, 'sup' => $expected), $this->element->getValue());
    }
    
    public function testDatetimeHumanFormatting()
    {
        $this->element->setIncludeTime(false);
        $this->assertEquals('jj/mm/aaaa', $this->element->getDatetimeFormatHuman());
        
        $this->element->setIncludeTime(true)
                      ->setDateTimeSeparator(' | ');
        $this->assertEquals('jj/mm/aaaa | hh:mm', $this->element->getDatetimeFormatHuman());
    }
    
    public function testGettingInputFilterWhenNoneIsSetReturnsDefaultOne()
    {
        $this->element->setInputFilter(null);
        $this->assertInstanceOf('UnicaenApp\Form\Element\DateInfSupInputFilter', $this->element->getInputFilter());
    }
    
    /**
     * @param type $messages
     */
    public function testSettingMessages()
    {
        $this->element->setMessages(array(
            $msg1 = "Même erreur sur les 2 dates"));
        $this->assertEquals(array(
            'inf' => $msg1, 
            'sup' => $msg1), $this->element->getMessages());
        
        $this->element->setMessages(array(
            'inf' => $msg1 = "Erreur sur date inf uniquement"));
        $this->assertEquals(array(
            'inf' => $msg1), $this->element->getMessages());
        
        $this->element->setMessages(array(
            'sup' => $msg2 = "Erreur sur date sup uniquement"));
        $this->assertEquals(array(
            'sup' => $msg2), $this->element->getMessages());
        
        $this->element->setMessages(array(
            $msg1 = "Erreur sur date inf", 
            $msg2 = "Erreur sur date sup"));
        $this->assertEquals(array(
            'inf' => $msg1, 
            'sup' => $msg2), $this->element->getMessages());
        
        $this->element->setMessages(array(
            'inf' => $msg1 = "Erreur sur date inf", 
            'sup' => $msg2 = "Erreur sur date sup"));
        $this->assertEquals(array(
            'inf' => $msg1, 
            'sup' => $msg2), $this->element->getMessages());
        
        $this->element->setMessages(array(
            $msg1 = "Erreur sur les 2 dates", 
            $msg2 = "Erreur sur les 2 dates", 
            $msg3 = "Erreur sur les 2 dates"));
        $this->assertEquals(array(
            'inf' => $idem = array($msg1, $msg2, $msg3), 
            'sup' => $idem), $this->element->getMessages());
    }
}