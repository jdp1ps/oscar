<?php
namespace UnicaenAppTest\Form\Element;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Form\Element\SearchAndSelect;

/**
 * Description of SearchAndSelectTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class SearchAndSelectTest extends PHPUnit_Framework_TestCase
{
    protected $element;
    
    protected function setUp()
    {
        $this->element = new SearchAndSelect('people');
    }
    
    public function getInvalidData()
    {
        return array(
            'integer'         => array(9),
            'string'          => array('string'),
            'unexpected_key'  => array(array('xyz' => 'value')),
            'id-key-alone'    => array(array('id' => '6544')),
            'label-key-alone' => array(array('label' => 'Paul Hochon')),
        );
    }
    
    public function testSelectionRequiredProperty()
    {
        $this->assertFalse($this->element->getSelectionRequired());
        
        $this->element->setSelectionRequired(true);
        $this->assertTrue($this->element->getSelectionRequired());
    }
    
    /**
     * @dataProvider getInvalidData
     * @expectedException \InvalidArgumentException
     */
    public function testSettingValueWithInvalidDataThrowsException($data)
    {
        $this->element->setValue($data);
    }
    
    public function testCanSetAutocompleteSource()
    {
        $this->element->setAutocompleteSource($url = '/url/to/autocomplete/action');
        $this->assertEquals($url, $this->element->getAutocompleteSource());
    }
    
    public function provideValue()
    {
        return array(
            array(null, null, null, null),
            array(array(), null, null, null),
            array(array('id' => $id = '123456', 'label' => $label = 'Paul Hochon'), $id, $label, '123456;Paul Hochon'),
            array(sprintf("%s|%s", $id = '123456', $label = 'Paul Hochon'), $id, $label, '123456;Paul Hochon'),
        );
    }
    
    /**
     * 
     * @param mixed $value
     * @param mixed $expectedValueId
     * @param mixed $expectedValueLabel
     * @param string $expectedImplodedValue
     * @dataProvider provideValue
     */
    public function testSettingValueSetsCompositeValue($value, $expectedValueId, $expectedValueLabel, $expectedImplodedValue)
    {
        $this->element->setValue($value);
        $this->assertEquals($expectedValueId, $this->element->getValueId());
        $this->assertEquals($expectedValueLabel, $this->element->getValueLabel());
        $this->assertEquals($expectedImplodedValue, $this->element->getValueImplode(';'));
    }
    
    public function testSettingValuesSetsCompositeValue()
    {
        $this->element->setValue($id = '123456', $label = 'Paul Hochon');
        $this->assertEquals($id, $this->element->getValueId());
        $this->assertEquals($label, $this->element->getValueLabel());
        $this->assertEquals(sprintf("%s;%s", $id, $label), $this->element->getValueImplode(';'));
    }
    
    public function testGettingValueReturnsIdValue()
    {
        $this->element->setValue(array('id' => $id = '123456', 'label' => 'Paul Hochon'));
        $this->assertEquals($id, $this->element->getValue());
    }
    
    public function getValidDataWithEmptyId()
    {
        return array(
            'null-id'  => array(array('id' => null, 'label' => $label = 'Paul Hochon')),
            'empty-id' => array(array('id' => '', 'label' => $label)),
            'zero-id'  => array(array('id' => 0, 'label' => $label)),
        );
    }
    
    /**
     * @dataProvider getValidDataWithEmptyId
     */
    public function testGettingValueReturnsLabelValueIfIdValueIsEmpty($data)
    {
        $this->element->setValue($data);
        $this->assertEquals($this->element->getValueLabel(), $this->element->getValue(true));
    }
    
    public function testSettingSelectionRequiredPropertyChangesInputSpecificationArray()
    {
        $this->element->setSelectionRequired(false);
        $inputSpec = $this->element->getInputSpecification();
        $validatorNotRequired = $inputSpec['validators'][0];
        $this->assertInternalType('array', $inputSpec);
        $this->assertFalse($inputSpec['required']);
        $this->assertInstanceOf('Zend\Validator\Callback', $validatorNotRequired);
        
        $this->element->setSelectionRequired(true);
        $inputSpec = $this->element->getInputSpecification();
        $validatorRequired = $inputSpec['validators'][0];
        $this->assertInternalType('array', $inputSpec);
        $this->assertFalse($inputSpec['required']);
        $this->assertInstanceOf('Zend\Validator\Callback', $validatorRequired);
        $this->assertNotSame($validatorNotRequired, $validatorRequired);
    }
    
    public function getValidData()
    {
        return array(
            'elemNotRequired-selNotRequired-emptyData' => array(
                $required          = false,
                $selectionRequired = false,
                $data              = array('id'    => '', 'label' => ''),
                $valid             = true,
            ),
            'elemNotRequired-selRequired-emptyId'      => array(
                $required          = false,
                $selectionRequired = true,
                $data              = array('id'    => '', 'label' => 'hochon'),
                $valid             = true,
            ),
            'elemRequired-selNotRequired-emptyData'    => array(
                $required          = true,
                $selectionRequired = false,
                $data              = array('id'    => '', 'label' => ''),
                $valid             = true,
            ),
            'elemRequired-selRequired-emptyId'         => array(
                $required          = true,
                $selectionRequired = true,
                $data              = array('id'    => '', 'label' => 'hochon'),
                $valid             = false,
            ),
            'elemRequired-selRequired'                 => array(
                $required          = true,
                $selectionRequired = true,
                $data              = array('id'    => '1234', 'label' => ''),
                $valid             = true,
            ),
        );
    }
    
    /**
     * @dataProvider getValidData
     * @param bool $required
     * @param bool $selectionRequired
     * @param array $data
     * @param bool $valid
     */
    public function testSelectionRequirementValidator($required, $selectionRequired, $data, $valid)
    {
        $validator = $this->element
                ->setRequired($required)
                ->setSelectionRequired($selectionRequired)
                ->getSelectionRequirementValidator();
        
        $this->assertEquals($valid, $validator->isValid($data));
    }
}