<?php
namespace UnicaenAppTest\Form\Element;

use DateTime;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use UnicaenApp\Form\Element\DateInfSup;
use UnicaenApp\Form\Element\DateInfSupInputFilter;
use UnicaenApp\Validator\EarlierThan;
use UnicaenApp\Validator\LaterThan;
use Zend\Validator\Date;

/**
 * Description of DateInfSupInputFilterTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class DateInfSupInputFilterTest extends PHPUnit_Framework_TestCase
{
    const DATE_FORMAT     = 'd/m/Y';
    const DATETIME_FORMAT = 'd/m/Y à H:i';
    
    protected $if;
    
    protected function setUp()
    {
        $this->if = new DateInfSupInputFilter(self::DATE_FORMAT);
    }
    
    public function testConstructorAddsInputs()
    {
        $this->assertCount(2, $this->if);
    }
    
    public function testInitCreatesInputsAndAddsValidators()
    {
        $this->if->init();
        $this->assertCount(2, $this->if);
        foreach ($this->if as $input) {
            $this->assertNotEmpty($input->getValidatorChain()->getValidators());
        }
    }
    
    public function testDatetimeHumanFormatting()
    {
        $this->if = new DateInfSupInputFilter('d/m/Y vers H:i');
        $this->assertEquals('jj/mm/aaaa vers hh:mm', $this->if->getDatetimeFormatHuman());
    }
    
    public function testCallingInitTwiceDoesNotRecreateInputs()
    {
        $this->if->init();
        $inputInf = $this->if->get(DateInfSup::DATE_INF_ELEMENT_NAME);
        $inputSup = $this->if->get(DateInfSup::DATE_SUP_ELEMENT_NAME);
        $this->if->init();
        $this->assertSame($inputInf, $this->if->get(DateInfSup::DATE_INF_ELEMENT_NAME));
        $this->assertSame($inputSup, $this->if->get(DateInfSup::DATE_SUP_ELEMENT_NAME));
    }
    
    public function getDatetimeFormatAndAttribute()
    {
        return array(
            array(self::DATE_FORMAT,     '11/03/2013',         '20/03/2013'),
            array(self::DATETIME_FORMAT, '11/03/2013 à 15:00', '20/03/2013 à 09:00'),
        );
    }
    
    /**
     * @dataProvider getDatetimeFormatAndAttribute
     * @param string $format
     * @param string $date1
     * @param string $date2
     */
    public function testChangingPropertiesUpdatesInputValidatorChains($format, $date1, $date2)
    {
        $this->if->setDatetimeFormat($format) // initial properties
                 ->setDateInfMin($date1)      //
                 ->init();
        $inputInfValidatorChain = $this->if->get(DateInfSup::DATE_INF_ELEMENT_NAME)->getValidatorChain();
        $inputSupValidatorChain = $this->if->get(DateInfSup::DATE_SUP_ELEMENT_NAME)->getValidatorChain();
        
        $this->if->init(); // initial properties not modified
        $this->assertSame($inputInfValidatorChain, $this->if->get(DateInfSup::DATE_INF_ELEMENT_NAME)->getValidatorChain());
        $this->assertSame($inputSupValidatorChain, $this->if->get(DateInfSup::DATE_SUP_ELEMENT_NAME)->getValidatorChain());
        
        $this->if->setDateInfMin($date2)  // initial propertie modified!
                 ->init();
        $this->assertNotSame($inputInfValidatorChain, $this->if->get(DateInfSup::DATE_INF_ELEMENT_NAME)->getValidatorChain());
        $this->assertNotSame($inputSupValidatorChain, $this->if->get(DateInfSup::DATE_SUP_ELEMENT_NAME)->getValidatorChain());
    }
    
    public function testValidatingAfterChangingPropertiesUpdatesAnteriorityValidator()
    {
        $this->if->setDatetimeFormat($format = self::DATE_FORMAT)
                 ->setData(array('inf' => '11/03/2013', 'sup' => '12/03/2013'))
                 ->isValid();
        $old = $this->readAttribute($this->if, 'anteriorityValidator');
        $this->assertEquals('12/03/2013', $old->getMax()->format($format));
        
        $this->if->setData(array('inf' => '01/03/2013', 'sup' => '05/03/2013'))
                 ->isValid();
        $new = $this->readAttribute($this->if, 'anteriorityValidator');
        $this->assertSame($old, $new);
        $this->assertEquals('05/03/2013', $new->getMax()->format($format));
    }
    
    public function getDateBoundary()
    {
        return array(
            // sans heure
            array(self::DATE_FORMAT, 'setDateInfMin', '11/02/2013 à 15:37'), // avec heure
            array(self::DATE_FORMAT, 'setDateInfMin', '11.02.2013'), // séparateur jma incorrect
            
            array(self::DATE_FORMAT, 'setDateInfMax', '11/02/2013 à 15:37'), // avec heure
            array(self::DATE_FORMAT, 'setDateInfMax', '11.02.2013'), // séparateur jma incorrect
            
            array(self::DATE_FORMAT, 'setDateSupMin', '11/02/2013 à 15:37'), // avec heure
            array(self::DATE_FORMAT, 'setDateSupMin', '11.02.2013'), // séparateur jma incorrect
            
            array(self::DATE_FORMAT, 'setDateSupMax', '11/02/2013 à 15:37'), // avec heure
            array(self::DATE_FORMAT, 'setDateSupMax', '11.02.2013'), // séparateur jma incorrect
            
            // avec heure
            array(self::DATETIME_FORMAT, 'setDateInfMin', '11/02/2013'), // pas d'heure
            array(self::DATETIME_FORMAT, 'setDateInfMin', '11/02/2013 @ 15:37'), // séparateur date-heure incorrect
            array(self::DATETIME_FORMAT, 'setDateInfMin', '11/02/2013 à 1537'), // pas de séparateur heure
            
            array(self::DATETIME_FORMAT, 'setDateInfMax', '11/02/2013'), // pas d'heure
            array(self::DATETIME_FORMAT, 'setDateInfMax', '11/02/2013 @ 15:37'), // séparateur date-heure incorrect
            array(self::DATETIME_FORMAT, 'setDateInfMax', '11/02/2013 à 1537'), // pas de séparateur heure
            
            array(self::DATETIME_FORMAT, 'setDateSupMin', '11/02/2013'), // pas d'heure
            array(self::DATETIME_FORMAT, 'setDateSupMin', '11/02/2013 @ 15:37'), // séparateur date-heure incorrect
            array(self::DATETIME_FORMAT, 'setDateSupMin', '11/02/2013 à 1537'), // pas de séparateur heure
            
            array(self::DATETIME_FORMAT, 'setDateSupMax', '11/02/2013'), // pas d'heure
            array(self::DATETIME_FORMAT, 'setDateSupMax', '11/02/2013 @ 15:37'), // séparateur date-heure incorrect
            array(self::DATETIME_FORMAT, 'setDateSupMax', '11/02/2013 à 1537'), // pas de séparateur heure
        );
    }
    
    /**
     * @dataProvider getDateBoundary
     * @expectedException InvalidArgumentException
     * @param string $format
     * @param string $method
     * @param string $boundary
     */
    public function testSettingDateBoundaryWithInvalidValueThrowsException($format, $method, $boundary)
    {
        $this->if->setDatetimeFormat($format)->$method($boundary);
        // NB: '31/02/2013' est valide et équivaut à '03/03/2013'
    }
    
    public function getDatasetWithEmptyDates()
    {
        return array(
            // sans heure
            // date inf requise, date sup non requise, date inf non fournie
            array(
                self::DATE_FORMAT,
                $dateInfRequired = true, 
                $dateSupRequired = false, 
                array(), 
                $expectedMessages = array('inf' => array('isEmpty' => "La date inférieure est requise"))),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => null), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => ''), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => null), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => ''), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => '12/03/2013'), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => '', 'sup' => '12/03/2013'), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => null, 'sup' => '12/03/2013'), 
                $expectedMessages),
            // date inf non requise, date sup requise, date inf non fournie
            array(
                self::DATE_FORMAT,
                $dateInfRequired = false, 
                $dateSupRequired = true, 
                array(), 
                $expectedMessages = array('sup' => array('isEmpty' => "La date supérieure est requise"))),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => null), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => ''), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => null), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => ''), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => '12/03/2013'), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => '', 'inf' => '12/03/2013'), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => null, 'inf' => '12/03/2013'), 
                $expectedMessages),
            // date inf requise, date sup requise, date inf et sup non fournies
            array(
                self::DATE_FORMAT,
                $dateInfRequired = true, 
                $dateSupRequired = true, 
                array(), 
                $expectedMessages = array(
                    'inf' => array('isEmpty' => "La date inférieure est requise"),
                    'sup' => array('isEmpty' => "La date supérieure est requise"))),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => null), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => ''), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => null), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => ''), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => null, 'sup' => null), 
                $expectedMessages),
            array(
                self::DATE_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => '', 'sup' => ''), 
                $expectedMessages),
            
            // avec heure
            // date inf requise, date sup non requise, date inf non fournie
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired = true, 
                $dateSupRequired = false, 
                array(), 
                $expectedMessages = array('inf' => array('isEmpty' => "La date inférieure est requise"))),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => null), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => ''), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => null), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => ''), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => '12/03/2013 à 15:37'), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => '', 'sup' => '12/03/2013 à 15:37'), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => null, 'sup' => '12/03/2013 à 15:37'), 
                $expectedMessages),
            // date inf non requise, date sup requise, date inf non fournie
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired = false, 
                $dateSupRequired = true, 
                array(), 
                $expectedMessages = array('sup' => array('isEmpty' => "La date supérieure est requise"))),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => null), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => ''), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => null), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => ''), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => '12/03/2013 à 15:37'), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => '', 'inf' => '12/03/2013 à 15:37'), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => null, 'inf' => '12/03/2013 à 15:37'), 
                $expectedMessages),
            // date inf requise, date sup requise, date inf et sup non fournies
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired = true, 
                $dateSupRequired = true, 
                array(), 
                $expectedMessages = array(
                    'inf' => array('isEmpty' => "La date inférieure est requise"),
                    'sup' => array('isEmpty' => "La date supérieure est requise"))),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => null), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => ''), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => null), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('sup' => ''), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => null, 'sup' => null), 
                $expectedMessages),
            array(
                self::DATETIME_FORMAT,
                $dateInfRequired, 
                $dateSupRequired, 
                array('inf' => '', 'sup' => ''), 
                $expectedMessages),
        );
    }
    
    /**
     * @dataProvider getDatasetWithEmptyDates
     * @param string $format
     * @param bool $dateInfRequired
     * @param bool $dateSupRequired
     * @param array $dataset
     * @param array $expectedMessages
     */
    public function testValidationOnDatasetWithEmptyDates($format, $dateInfRequired, $dateSupRequired, $dataset, $expectedMessages)
    {
        $this->if->setDatetimeFormat($format)
                 ->setDateInfRequired($dateInfRequired)
                 ->setDateSupRequired($dateSupRequired)
                 ->init()
                 ->setData($dataset);
        $this->assertFalse($this->if->isValid());
        $this->assertEquals($expectedMessages, $this->if->getMessages());
    }
    
    public function getDatasetWithMalformedDates()
    {
        return array(
            // format sans heure
            // format date inf invalide 
            'date-inf-31-fevrier' => array(
                self::DATE_FORMAT,
                array('inf' => '31/02/2013'),
                $expectedMessages = array(
                    'inf' => array(Date::INVALID_DATE => "La date inférieure spécifiée n'est pas valide")
                ),
            ),
            'date-inf-separateur-jma-incorrect' => array(
                self::DATE_FORMAT,
                array('inf' => '13.02.2013'),
                $expectedMessages,
            ),
            // format date sup invalide 
            'date-sup-31-fevrier' => array(
                self::DATE_FORMAT,
                array('sup' => '31/02/2013'),
                $expectedMessages = array(
                    'sup' => array(Date::INVALID_DATE => "La date supérieure spécifiée n'est pas valide")
                ),
            ),
            'date-sup-separateur-jma-incorrect' => array(
                self::DATE_FORMAT,
                array('sup' => '13.02.2013'), 
                $expectedMessages,
            ),
            // format dates inf et sup invalide
            'date-inf-31-fevrier_date-sup-separateur-jma-incorrect' => array(
                self::DATE_FORMAT,
                array('inf' => '31/02/2013', 'sup' => '13.02.2013'),
                $expectedMessages = array(
                    'inf' => array(Date::INVALID_DATE => "La date inférieure spécifiée n'est pas valide"),
                    'sup' => array(Date::INVALID_DATE => "La date supérieure spécifiée n'est pas valide")
                )
            ),
            
            // format avec heure
            // format date inf invalide 
            'date-inf-sans-heure' => array(
                self::DATETIME_FORMAT,
                array('inf' => '11/02/2013'),
                $expectedMessages = array(
                    'inf' => array(Date::INVALID_DATE => "La date inférieure spécifiée n'est pas valide")
                )
            ),
            'date-inf-31-fevrier' => array(
                self::DATETIME_FORMAT,
                array('inf' => '31/02/2013 à 15:37'),
                $expectedMessages,
            ),
            'date-inf-25-heure' => array(
                self::DATETIME_FORMAT,
                array('inf' => '11/02/2013 à 25:37'),
                $expectedMessages,
            ),
            'date-inf-separateur-date-heure-incorrect' => array(
                self::DATETIME_FORMAT,
                array('inf' => '12/03/2013 @ 15:37'),
                $expectedMessages,
            ),
            'date-inf-separateur-jma-incorrect' => array(
                self::DATETIME_FORMAT,
                array('inf' => '13.02.2013 à 15:37'), 
                $expectedMessages,
            ),
            // format date sup invalide 
            'date-sup-sans-heure' => array(
                self::DATETIME_FORMAT,
                array('sup' => '11/02/2013'),
                $expectedMessages = array(
                    'sup' => array(Date::INVALID_DATE => "La date supérieure spécifiée n'est pas valide")
                )
            ),
            'date-sup-31-fevrier' => array(
                self::DATETIME_FORMAT,
                array('sup' => '31/02/2013 à 15:37'),
                $expectedMessages,
            ),
            'date-sup-25-heure' => array(
                self::DATETIME_FORMAT,
                array('sup' => '11/02/2013 à 25:37'),
                $expectedMessages,
            ),
            'date-sup-separateur-date-heure-incorrect' => array(
                self::DATETIME_FORMAT,
                array('sup' => '12/03/2013 @ 15:37'),
                $expectedMessages,
            ),
            'date-sup-separateur-jma-incorrect' => array(
                self::DATETIME_FORMAT,
                array('sup' => '13.02.2013 à 15:37'), 
                $expectedMessages,
            ),
            // format dates inf et sup invalide
            'date-inf-sans-heure_date-sup-31-fevrier' => array(
                self::DATETIME_FORMAT,
                array('inf' => '11/02/2013', 'sup' => '31/02/2013 à 15:37'),
                $expectedMessages = array(
                    'inf' => array(Date::INVALID_DATE => "La date inférieure spécifiée n'est pas valide"),
                    'sup' => array(Date::INVALID_DATE => "La date supérieure spécifiée n'est pas valide")
                )
            ),
            'date-inf-25-heure_date-inf-separateur-date-heure-incorrect' => array(
                self::DATETIME_FORMAT,
                array('inf' => '11/02/2013 à 25:37', 'sup' => '31/02/2013 @ 15:37'),
                $expectedMessages,
            ),
        );
    }
    
    /**
     * @dataProvider getDatasetWithMalformedDates
     * @param string $format
     * @param array $dataset
     * @param array $expectedMessages
     */
    public function testValidationOnDatasetWithMalformedDates($format, $dataset, $expectedMessages)
    {
        $this->if->setDatetimeFormat($format)
                 ->setDateInfRequired(false)
                 ->setDateSupRequired(false)
                 ->init()
                 ->setData($dataset);
        $this->assertFalse($this->if->isValid());
        $this->assertEquals($expectedMessages, $this->if->getMessages());
    }
    
    public function getDatasetWithBadAnteriorityDates()
    {
        return array(
            // sans heure
            array(
                self::DATE_FORMAT,
                array('inf' => '11/03/2013', 'sup' => $dateSup = '10/03/2013'),
                $expectedMessages = array(
                    'inf' => array(EarlierThan::NOT_LESS_INCLUSIVE => "La date spécifiée n'est pas antérieure ou égale au '$dateSup'")
                )
            ),
            // avec heure
            array(
                self::DATETIME_FORMAT,
                array('inf' => '11/03/2013 à 15:37', 'sup' => $dateSup = '10/03/2013 à 15:37'),
                $expectedMessages = array(
                    'inf' => array(EarlierThan::NOT_LESS_INCLUSIVE => "La date spécifiée n'est pas antérieure ou égale au '$dateSup'")
                )
            ),
            array(
                self::DATETIME_FORMAT,
                array('inf' => '11/03/2013 à 15:37', 'sup' => $dateSup = '11/03/2013 à 15:36'),
                $expectedMessages = array(
                    'inf' => array(EarlierThan::NOT_LESS_INCLUSIVE => "La date spécifiée n'est pas antérieure ou égale au '$dateSup'")
                )
            ),
        );
    }
    
    /**
     * @dataProvider getDatasetWithBadAnteriorityDates
     * @param string $format
     * @param array $dataset
     * @param array $expectedMessages
     */
    public function testValidationOnDatasetWithBadAnteriorityDates($format, $dataset, $expectedMessages)
    {
        $this->if->setDatetimeFormat($format)
                 ->setDateInfRequired(false)
                 ->setDateSupRequired(false)
                 ->init()
                 ->setData($dataset);
        $this->assertFalse($this->if->isValid());
        $this->assertEquals($expectedMessages, $this->if->getMessages());
    }
    
    public function getDatasetWithOutOfBoundDates()
    {
        return array(
            // sans heure
            // date inf trop petite
            array(
                self::DATE_FORMAT,
                $dateInfMin = '11/03/2013',
                $dateInfMax = null,
                $dateSupMin = null,
                $dateSupMax = null,
                $dataset = array('inf' => '10/03/2013'),
                $expectedMessages = array(
                    'inf' => array(LaterThan::NOT_GREATER => "La date inférieure spécifiée n'est pas postérieure au '$dateInfMin'")
                )
            ),
            // date inf trop grande
            array(
                self::DATE_FORMAT,
                $dateInfMin = null,
                $dateInfMax = '11/03/2013',
                $dateSupMin = null,
                $dateSupMax = null,
                $dataset = array('inf' => '12/03/2013'),
                $expectedMessages = array(
                    'inf' => array(EarlierThan::NOT_LESS => "La date inférieure spécifiée n'est pas antérieure au '$dateInfMax'")
                )
            ),
            // date sup trop petite
            array(
                self::DATE_FORMAT,
                $dateInfMin = null,
                $dateInfMax = null,
                $dateSupMin = '11/03/2013',
                $dateSupMax = null,
                $dataset = array('sup' => '10/03/2013'),
                $expectedMessages = array(
                    'sup' => array(LaterThan::NOT_GREATER => "La date supérieure spécifiée n'est pas postérieure au '$dateSupMin'")
                )
            ),
            // date sup trop grande
            array(
                self::DATE_FORMAT,
                $dateInfMin = null,
                $dateInfMax = null,
                $dateSupMin = null,
                $dateSupMax = '11/03/2013',
                $dataset = array('sup' => '12/03/2013'),
                $expectedMessages = array(
                    'sup' => array(EarlierThan::NOT_LESS => "La date supérieure spécifiée n'est pas antérieure au '$dateSupMax'")
                )
            ),
            // dates inf et sup trop petites
            array(
                self::DATE_FORMAT,
                $dateInfMin = '10/03/2013',
                $dateInfMax = null,
                $dateSupMin = '13/03/2013',
                $dateSupMax = null,
                $dataset = array('inf' => '09/03/2013', 'sup' => '11/03/2013'),
                $expectedMessages = array(
                    'inf' => array(LaterThan::NOT_GREATER => "La date inférieure spécifiée n'est pas postérieure au '$dateInfMin'"),
                    'sup' => array(LaterThan::NOT_GREATER => "La date supérieure spécifiée n'est pas postérieure au '$dateSupMin'")
                )
            ),
            // dates inf et sup trop grandes
            array(
                self::DATE_FORMAT,
                $dateInfMin = null,
                $dateInfMax = '10/03/2013',
                $dateSupMin = null,
                $dateSupMax = '13/03/2013',
                $dataset = array('inf' => '11/03/2013', 'sup' => '14/03/2013'),
                $expectedMessages = array(
                    'inf' => array(EarlierThan::NOT_LESS => "La date inférieure spécifiée n'est pas antérieure au '$dateInfMax'"),
                    'sup' => array(EarlierThan::NOT_LESS => "La date supérieure spécifiée n'est pas antérieure au '$dateSupMax'")
                )
            ),
            // date inf top petite et date sup trop grande
            array(
                self::DATE_FORMAT,
                $dateInfMin = '11/03/2013',
                $dateInfMax = null,
                $dateSupMin = null,
                $dateSupMax = '12/03/2013',
                $dataset = array('inf' => '10/03/2013', 'sup' => '13/03/2013'),
                $expectedMessages = array(
                    'inf' => array(LaterThan::NOT_GREATER => "La date inférieure spécifiée n'est pas postérieure au '$dateInfMin'"),
                    'sup' => array(EarlierThan::NOT_LESS => "La date supérieure spécifiée n'est pas antérieure au '$dateSupMax'")
                )
            ),
            // date inf top grande et date sup trop petite
            array(
                self::DATE_FORMAT,
                $dateInfMin = null,
                $dateInfMax = '10/03/2013',
                $dateSupMin = '13/03/2013',
                $dateSupMax = null,
                $dataset = array('inf' => '11/03/2013', 'sup' => '12/03/2013'),
                $expectedMessages = array(
                    'inf' => array(EarlierThan::NOT_LESS => "La date inférieure spécifiée n'est pas antérieure au '$dateInfMax'"),
                    'sup' => array(LaterThan::NOT_GREATER => "La date supérieure spécifiée n'est pas postérieure au '$dateSupMin'")
                )
            ),
            
            
            // sans heure
            // date inf trop petite
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = '11/03/2013 à 09:00',
                $dateInfMax = null,
                $dateSupMin = null,
                $dateSupMax = null,
                $dataset = array('inf' => '11/03/2013 à 08:37'),
                $expectedMessages = array(
                    'inf' => array(LaterThan::NOT_GREATER => "La date inférieure spécifiée n'est pas postérieure au '$dateInfMin'")
                )
            ),
            array(
                self::DATETIME_FORMAT,
                $dateInfMin,
                $dateInfMax = '11/03/2013 à 17:00',
                $dateSupMin = null,
                $dateSupMax = null,
                $dataset,
                $expectedMessages
            ),
            // date inf trop grande
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = null,
                $dateInfMax = '11/03/2013 à 17:00',
                $dateSupMin = null,
                $dateSupMax = null,
                $dataset = array('inf' => '11/03/2013 à 18:12'),
                $expectedMessages = array(
                    'inf' => array(EarlierThan::NOT_LESS => "La date inférieure spécifiée n'est pas antérieure au '$dateInfMax'")
                )
            ),
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = '11/03/2013 à 09:00',
                $dateInfMax,
                $dateSupMin = null,
                $dateSupMax = null,
                $dataset,
                $expectedMessages
            ),
            
            // date sup trop petite
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = null,
                $dateInfMax = null,
                $dateSupMin = '11/03/2013 à 09:00',
                $dateSupMax = null,
                $dataset = array('sup' => '11/03/2013 à 08:37'),
                $expectedMessages = array(
                    'sup' => array(LaterThan::NOT_GREATER => "La date supérieure spécifiée n'est pas postérieure au '$dateSupMin'")
                )
            ),
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = null,
                $dateInfMax = null,
                $dateSupMin,
                $dateSupMax = '11/03/2013 à 17:00',
                $dataset,
                $expectedMessages
            ),
            // date sup trop grande
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = null,
                $dateInfMax = null,
                $dateSupMin = null,
                $dateSupMax = '11/03/2013 à 17:00',
                $dataset = array('sup' => '11/03/2013 à 18:12'),
                $expectedMessages = array(
                    'sup' => array(EarlierThan::NOT_LESS => "La date supérieure spécifiée n'est pas antérieure au '$dateSupMax'")
                )
            ),
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = null,
                $dateInfMax = null,
                $dateSupMin = '11/03/2013 à 09:00',
                $dateSupMax,
                $dataset,
                $expectedMessages
            ),
            
            // dates inf et sup trop petites
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = '11/03/2013 à 09:00',
                $dateInfMax = null,
                $dateSupMin = '12/03/2013 à 09:00',
                $dateSupMax = null,
                $dataset = array('inf' => '11/03/2013 à 08:37', 'sup' => '11/03/2013 à 18:37'),
                $expectedMessages = array(
                    'inf' => array(LaterThan::NOT_GREATER => "La date inférieure spécifiée n'est pas postérieure au '$dateInfMin'"),
                    'sup' => array(LaterThan::NOT_GREATER => "La date supérieure spécifiée n'est pas postérieure au '$dateSupMin'")
                )
            ),
            // dates inf et sup trop grandes
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = null,
                $dateInfMax = '11/03/2013 à 17:00',
                $dateSupMin = null,
                $dateSupMax = '12/03/2013 à 17:00',
                $dataset = array('inf' => '11/03/2013 à 18:37', 'sup' => '12/03/2013 à 18:37'),
                $expectedMessages = array(
                    'inf' => array(EarlierThan::NOT_LESS => "La date inférieure spécifiée n'est pas antérieure au '$dateInfMax'"),
                    'sup' => array(EarlierThan::NOT_LESS => "La date supérieure spécifiée n'est pas antérieure au '$dateSupMax'")
                )
            ),
            // date inf top petite et date sup trop grande
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = '11/03/2013 à 09:00',
                $dateInfMax = null,
                $dateSupMin = null,
                $dateSupMax = '12/03/2013 à 17:00',
                $dataset = array('inf' => '11/03/2013 à 08:37', 'sup' => '12/03/2013 à 18:37'),
                $expectedMessages = array(
                    'inf' => array(LaterThan::NOT_GREATER => "La date inférieure spécifiée n'est pas postérieure au '$dateInfMin'"),
                    'sup' => array(EarlierThan::NOT_LESS => "La date supérieure spécifiée n'est pas antérieure au '$dateSupMax'")
                )
            ),
            // date inf top grande et date sup trop petite
            array(
                self::DATETIME_FORMAT,
                $dateInfMin = null,
                $dateInfMax = '11/03/2013 à 17:00',
                $dateSupMin = '12/03/2013 à 09:00',
                $dateSupMax = null,
                $dataset = array('inf' => '11/03/2013 à 18:37', 'sup' => '12/03/2013 à 08:37'),
                $expectedMessages = array(
                    'inf' => array(EarlierThan::NOT_LESS => "La date inférieure spécifiée n'est pas antérieure au '$dateInfMax'"),
                    'sup' => array(LaterThan::NOT_GREATER => "La date supérieure spécifiée n'est pas postérieure au '$dateSupMin'")
                )
            ),
        );
    }
    
    /**
     * @dataProvider getDatasetWithOutOfBoundDates
     * @param string $format
     * @param DateTime $dateInfMin
     * @param DateTime $dateInfMax
     * @param DateTime $dateSupMin
     * @param DateTime $dateSupMax
     * @param array $dataset
     * @param array $expectedMessages
     */
    public function testValidationOnDatasetWithOutOfBoundDates(
            $format, 
            $dateInfMin, 
            $dateInfMax, 
            $dateSupMin,
            $dateSupMax,
            $dataset,
            $expectedMessages)
    {
        $this->if->setDatetimeFormat($format)
                 ->setDateInfRequired(false)
                 ->setDateSupRequired(false)
                 ->setDateInfMin($dateInfMin)
                 ->setDateInfMax($dateInfMax)
                 ->setDateSupMin($dateSupMin)
                 ->setDateSupMax($dateSupMax)
                 ->init()
                 ->setData($dataset);
        $this->assertFalse($this->if->isValid());
        $this->assertEquals($expectedMessages, $this->if->getMessages());
    }
}