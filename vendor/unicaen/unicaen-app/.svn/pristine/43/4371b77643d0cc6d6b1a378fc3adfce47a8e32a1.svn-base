<?php
namespace UnicaenAppTest\Form;

use PHPUnit_Framework_TestCase;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Form\MultipageForm;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Fieldset;

/**
 * Description of MultipageFormTest
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 * @see MultipageForm
 */
class MultipageFormTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MultipageForm
     */
    protected $form;
    
    protected function setUp()
    {
        $this->form = $this->getMockForAbstractClass('\UnicaenApp\Form\MultipageForm');
    }
    
    public function testAddingFirstFieldsetAddsFieldsetToFormAndAddsNavigationElementToFieldset()
    {
        $fieldset = new Fieldset('fs');
        $this->form->add($fieldset);
        $this->assertSame($fieldset, $this->form->get($fieldset->getName()));
        $this->assertTrue($fieldset->has(MultipageForm::NAME_NAV));
        $this->assertInstanceOf('UnicaenApp\Form\Element\MultipageFormNav', $elem = $fieldset->get(MultipageForm::NAME_NAV));
        $this->assertTrue($elem->getActivateCancel());
        $this->assertFalse($elem->getActivatePrevious());
        $this->assertTrue($elem->getActivateNext());
        $this->assertFalse($elem->getActivateSubmit());
    }
    
    /**
     * @expectedException LogicException
     */
    public function testAddingSameFirstFieldsetTwiceThrowsException()
    {
        $fieldset = new Fieldset('fs');
        $this->form->add($fieldset)
                   ->add($fieldset);
    }
    
    /**
     * @expectedException LogicException
     */
    public function testAddingSameNextFieldsetAsFirstOneThrowsException()
    {
        $fieldset = new Fieldset('fs');
        $this->form->add($fieldset)
                   ->add($fieldset);
    }
    
    public function testAddingNextFieldsetAddsFieldsetToFormAndAddsNavigationElementToFieldset()
    {
        $fieldset1 = new Fieldset('fs1');
        $fieldset2 = new Fieldset('fs2');
        $this->form->add($fieldset1)
                   ->add($fieldset2);
        $this->assertTrue($fieldset2->has(MultipageForm::NAME_NAV));
        $this->assertInstanceOf('UnicaenApp\Form\Element\MultipageFormNav', $elem = $fieldset2->get(MultipageForm::NAME_NAV));
        $this->assertTrue($elem->getActivateCancel());
        $this->assertTrue($elem->getActivatePrevious());
        $this->assertFalse($elem->getActivateNext());
        $this->assertTrue($elem->getActivateSubmit());
    }
    
    /**
     * @expectedException LogicException
     */
    public function testAddingSameNextFieldsetTwiceThrowsException()
    {
        $fieldset1 = new Fieldset('fs1');
        $fieldset2 = new Fieldset('fs2');
        $this->form->add($fieldset1)
                   ->add($fieldset2)
                   ->add($fieldset2);
    }
    
    /**
     * @expectedException LogicException
     */
    public function testAddingSameLastFieldsetAsFirstOneThrowsException()
    {
        $fieldset = new Fieldset('fs');
        $this->form->add($fieldset)
                   ->add($fieldset);
    }
    
    /**
     * @expectedException LogicException
     */
    public function testAddingSameLastFieldsetAsNextOneThrowsException()
    {
        $fieldset1 = new Fieldset('fs1');
        $fieldset2 = new Fieldset('fs2');
        $this->form->add($fieldset1)
                   ->add($fieldset2)
                   ->add($fieldset2);
    }
    
    /**
     * @expectedException LogicException
     */
    public function testAddingSameLastFieldsetTwiceThrowsException()
    {
        $fieldset1 = new Fieldset('fs1');
        $fieldset2 = new Fieldset('fs2');
        $this->form->add($fieldset1)
                   ->add($fieldset2)
                   ->add($fieldset2);
    }
    
    public function testAddingLastFieldsetAddsFieldsetToFormAndAddsNavigationElementToFieldset()
    {
        $fieldset1 = new Fieldset('fs1');
        $fieldset2 = new Fieldset('fs2');
        $this->form->add($fieldset1)
                   ->add($fieldset2);
        $this->assertTrue($fieldset2->has(MultipageForm::NAME_NAV));
        $this->assertInstanceOf('UnicaenApp\Form\Element\MultipageFormNav', $elem = $fieldset2->get(MultipageForm::NAME_NAV));
        $this->assertTrue($elem->getActivateCancel());
        $this->assertTrue($elem->getActivatePrevious());
        $this->assertFalse($elem->getActivateNext());
        $this->assertTrue($elem->getActivateSubmit());
    }
    
    public function testDefaultActionsAreNotEmpty()
    {
        $confirmAction = $this->form->getConfirmAction();
        $cancelAction  = $this->form->getCancelAction();
        $processAction = $this->form->getProcessAction();
        $this->assertNotEmpty($confirmAction);
        $this->assertNotEmpty($cancelAction);
        $this->assertNotEmpty($processAction);
    }
    
    public function testFieldsetActionMappingIsEmptyWhenNoFieldsetAreAdded()
    {
        $this->assertEmpty($this->form->getFieldsetActionMapping());
    }
    
    public function testFieldsetActionMappingDependsOnAddedFieldset()
    {
        $fieldset1 = new Fieldset('fs1');
        $fieldset2 = new Fieldset('fs2');
        $fieldset3 = new Fieldset('fs3');
        $this->form->add($fieldset1)
                   ->add($fieldset2)
                   ->add($fieldset3);
        $this->assertEquals(
                array(
                    $name = $fieldset1->getName() => $name,
                    $name = $fieldset2->getName() => $name,
                    $name = $fieldset3->getName() => $name,
                ), 
                $this->form->getFieldsetActionMapping());
    }
    
    public function testSpecifiedActionsArePrefixedByActionPrefix()
    {
        $this->form->setConfirmAction($confirmAction = 'confirm');
        $this->form->setCancelAction($cancelAction = 'cancel');
        $this->form->setProcessAction($processAction = 'process');
        $this->form->setActionPrefix($prefix = 'ajouter-');
        $this->assertEquals($prefix . $confirmAction, $this->form->getConfirmAction());
        $this->assertEquals($prefix . $cancelAction,  $this->form->getCancelAction());
        $this->assertEquals($prefix . $processAction, $this->form->getProcessAction());
    }
    
    public function testFieldsetActionsArePrefixedByActionPrefix()
    {
        $fieldset1 = new Fieldset('fs1');
        $fieldset2 = new Fieldset('fs2');
        $fieldset3 = new Fieldset('fs3');
        $this->form->add($fieldset1)
                   ->add($fieldset2)
                   ->add($fieldset3);
        $this->form->setActionPrefix($prefix = 'ajouter-');
        $this->assertEquals(
                array(
                    $name = $fieldset1->getName() => $prefix . $name,
                    $name = $fieldset2->getName() => $prefix . $name,
                    $name = $fieldset3->getName() => $prefix . $name,
                ), 
                $this->form->getFieldsetActionMapping());
    }
    
    public function testEnablingOrDisablingFieldsetActionForInvalidFieldsetThrowsException()
    {
        $fieldset1 = new Fieldset($name = 'fs1');
        $this->form->add($fieldset1);
        
        // fieldset specified as string
        try { 
            $this->form->setEnabledFieldsetCancel(5, true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetPrevious(5, true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetNext(5, true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetSubmit(5, true);
            $this->fail();
        } catch (Exception $exc) { }
        
        
        // fieldset specified as object
        $fieldset = new Fieldset('non_existing_fieldset_name');
        try { 
            $this->form->setEnabledFieldsetCancel($fieldset, true);
            $this->fail();
        } catch (Exception $exc) {}
        try { 
            $this->form->setEnabledFieldsetPrevious($fieldset, true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetNext($fieldset, true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetSubmit($fieldset, true);
            $this->fail();
        } catch (Exception $exc) { }
    }
    
    public function testEnablingOrDisablingFieldsetActionForNonExistingFieldsetThrowsException()
    {
        $fieldset1 = new Fieldset($name = 'fs1');
        $this->form->add($fieldset1);
        
        // fieldset specified as string
        try { 
            $this->form->setEnabledFieldsetCancel('non_existing_fieldset_name', true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetPrevious('non_existing_fieldset_name', true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetNext('non_existing_fieldset_name', true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetSubmit('non_existing_fieldset_name', true);
            $this->fail();
        } catch (Exception $exc) { }
        
        
        // fieldset specified as object
        $fieldset = new Fieldset('non_existing_fieldset_name');
        try { 
            $this->form->setEnabledFieldsetCancel($fieldset, true);
            $this->fail();
        } catch (Exception $exc) {}
        try { 
            $this->form->setEnabledFieldsetPrevious($fieldset, true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetNext($fieldset, true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetSubmit($fieldset, true);
            $this->fail();
        } catch (Exception $exc) { }
    }
    
    public function testEnablingOrDisablingFieldsetActionForFieldsetWithoutNavigationElementThrowsException()
    {
        $fieldset1 = new Fieldset($name = 'fs1');
        $this->form->add($fieldset1);
        $fieldset1->remove(MultipageForm::NAME_NAV);
        
        try { 
            $this->form->setEnabledFieldsetCancel($fieldset1, true);
            $this->fail();
        } catch (Exception $exc) {}
        try { 
            $this->form->setEnabledFieldsetPrevious($fieldset1, true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetNext($fieldset1, true);
            $this->fail();
        } catch (Exception $exc) { }
        try { 
            $this->form->setEnabledFieldsetSubmit($fieldset1, true);
            $this->fail();
        } catch (Exception $exc) { }
    }
    
    /**
     * @depends testAddingFirstFieldsetAddsFieldsetToFormAndAddsNavigationElementToFieldset
     * @depends testAddingNextFieldsetAddsFieldsetToFormAndAddsNavigationElementToFieldset
     * @depends testAddingLastFieldsetAddsFieldsetToFormAndAddsNavigationElementToFieldset
     */
    public function testEnablingOrDisablingFieldsetActionModifiesFieldsetNavigationElement()
    {
        $fieldset1 = new Fieldset($name = 'fs1');
        $this->form->add($fieldset1);
        $elem = $fieldset1->get(MultipageForm::NAME_NAV);
        
        foreach (array(true, false) as $enabled) {
            // fieldset as objet
            $this->form->setEnabledFieldsetCancel($fieldset1, $enabled);
            $this->form->setEnabledFieldsetPrevious($fieldset1, $enabled);
            $this->form->setEnabledFieldsetNext($fieldset1, $enabled);
            $this->form->setEnabledFieldsetSubmit($fieldset1, $enabled);
            $this->assertEquals($enabled, $elem->getActivateCancel());
            $this->assertEquals($enabled, $elem->getActivatePrevious());
            $this->assertEquals($enabled, $elem->getActivateNext());
            $this->assertEquals($enabled, $elem->getActivateSubmit());
            // fieldset as string
            $this->form->setEnabledFieldsetCancel($name, $enabled);
            $this->form->setEnabledFieldsetPrevious($name, $enabled);
            $this->form->setEnabledFieldsetNext($name, $enabled);
            $this->form->setEnabledFieldsetSubmit($name, $enabled);
            $this->assertEquals($enabled, $elem->getActivateCancel());
            $this->assertEquals($enabled, $elem->getActivatePrevious());
            $this->assertEquals($enabled, $elem->getActivateNext());
            $this->assertEquals($enabled, $elem->getActivateSubmit());
        }
    }
    
    public function testExtractingLabelsAndValuesFromFieldsetImplementingMultipageFormFieldsetInterfaceIsDelegatedToTheFieldset()
    {
        $fieldset = $this->getMock('UnicaenAppTest\Form\TestAsset\IdentiteFieldset', array('getLabelsAndValues'));
        $fieldset->setName('fs');
                
        $fieldset->expects($this->once())
                 ->method('getLabelsAndValues')
                 ->will($this->returnValue($expected = array('nom' => array('label' => "Nom", 'value' => "Paul Hochon"))));
        
        $this->assertEquals($expected, $this->form->getLabelsAndValues($fieldset));
    }
    
    public function testExtractingLabelsAndValuesFromEmptyFieldsetReturnsEmptyArray()
    {
        $fieldset = new Fieldset('fs');
        $this->assertEquals(array(), $this->form->getLabelsAndValues($fieldset));
    }
    
    public function testExtractingLabelsAndValuesWithNullDataRetrievesDataFromFieldsetItSelf()
    {
        $fieldset = $this->getMock('Zend\Form\Fieldset', array('getValue'));
        $fieldset->setName('fs');
                
        $fieldset->expects($this->once())
                 ->method('getValue')
                 ->will($this->returnValue(array()));
        
        $this->form->getLabelsAndValues($fieldset);
    }
    
    public function getEmptyDataForLabelsAndValuesExtraction()
    {
        return array(
            array(null),
            array(array()),
            array(array('fs' => array())),
            array(array('fs' => null)),
        );
    }
    
    /**
     * 
     * @param array $data
     * @dataProvider getEmptyDataForLabelsAndValuesExtraction
     */
    public function testExtractingLabelsAndValuesWithEmptyDataReturnsCorrectArray($data)
    {
        $fieldset = new Fieldset('fs');
        $fieldset->add(new Text($name1 = 'nom',    array('label' => $label1 = "Nom")));
        $fieldset->add(new Text($name2 = 'prenom', array('label' => $label2 = "Prénom")));
        
        $actual = $this->form->getLabelsAndValues($fieldset, $data);
        $expected = array(
            $name1 => array(
                'label' => $label1,
                'value' => "Non renseigné(e)",
            ),
            $name2 => array(
                'label' => $label2,
                'value' => "Non renseigné(e)",
            ),
        );
        $this->assertEquals($expected, $actual);
    }
    
    public function getIncompleteDataForLabelsAndValuesExtraction()
    {
        return array(
            array(array('nom' => 'Hochon', 'prenom' => '')),
            array(array('nom' => 'Hochon', 'prenom' => null)),
        );
    }
    
    /**
     * 
     * @param array $data
     * @dataProvider getIncompleteDataForLabelsAndValuesExtraction
     */
    public function testExtractingLabelsAndValuesWithIncompleteDataReturnsCorrectArray($data)
    {
        $fieldset = new Fieldset('fs');
        $fieldset->add(new Text($name1 = 'nom',    array('label' => $label1 = "Nom")));
        $fieldset->add(new Text($name2 = 'prenom', array('label' => $label2 = "Prénom")));
        
        $actual = $this->form->getLabelsAndValues($fieldset, $data);
        $expected = array(
            $name1 => array(
                'label' => $label1,
                'value' => "Hochon",
            ),
            $name2 => array(
                'label' => $label2,
                'value' => "Non renseigné(e)",
            ),
        );
        $this->assertEquals($expected, $actual);
    }
    
    public function getCompleteDataForLabelsAndValuesExtraction()
    {
        return array(
            array(array('nom' => 'Hochon', 'prenom' => 'Paul', 'civ' => 'M', 'agree' => 1)),
        );
    }
    
    /**
     * 
     * @param array $data
     * @dataProvider getCompleteDataForLabelsAndValuesExtraction
     */
    public function testExtractingLabelsAndValuesWithCompleteDataReturnsCorrectArray($data)
    {
        $fieldset = new Fieldset('fs');
        $fieldset->add(new Text($name1 = 'nom',    array('label' => $label1 = "Nom")));
        $fieldset->add(new Text($name2 = 'prenom', array('label' => $label2 = "Prénom")));
        
        $actual = $this->form->getLabelsAndValues($fieldset, $data);
        $expected = array(
            $name1 => array(
                'label' => $label1,
                'value' => "Hochon",
            ),
            $name2 => array(
                'label' => $label2,
                'value' => "Paul",
            ),
        );
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * 
     * @param array $data
     * @dataProvider getCompleteDataForLabelsAndValuesExtraction
     */
    public function testExtractingLabelsAndValuesWithDataContainingUnexistingElementKeyIgnoreDoesNotIncludeThisKey($data)
    {
        $fieldset = new Fieldset('fs');
        $fieldset->add(new Text('nom', array('label' => "Prénom")));
        $fieldset->add(new Text('prenom', array('label' => "Prénom")));
        
        $this->assertArrayNotHasKey('civ', $this->form->getLabelsAndValues($fieldset, $data));
    }
    
    /**
     * 
     * @param array $data
     * @dataProvider getCompleteDataForLabelsAndValuesExtraction
     */
    public function testExtractingLabelsAndValuesFromFieldsetHavingAnElementWithoutLabelSkipsThisElement($data)
    {
        $fieldset = new Fieldset('fs');
        $fieldset->add(new Text('nom'));
        $fieldset->add(new Text('prenom', array('label' => "Prénom")));
        
        $this->assertArrayNotHasKey('nom', $this->form->getLabelsAndValues($fieldset, $data));
    }
    
    /**
     * 
     * @param array $data
     * @dataProvider getCompleteDataForLabelsAndValuesExtraction
     */
    public function testExtractingLabelsAndValuesFromFieldsetHavingAMultiOptionsElement($data)
    {
        $fieldset = new Fieldset('fs');
        $fieldset->add(new Text($name1 = 'nom', array('label' => $label1 = "Prénom")));
        $fieldset->add(new Text($name2 = 'prenom', array('label' => $label2 = "Prénom")));
        $fieldset->add(new Select($name3 = 'civ', array('label' => $label3 = "Civilité", 'value_options' => array('M'=>'Monsieur', 'Mme'=>'Madame'))));
        
        $actual = $this->form->getLabelsAndValues($fieldset, $data);
        $expected = array(
            $name1 => array(
                'label' => $label1,
                'value' => "Hochon",
            ),
            $name2 => array(
                'label' => $label2,
                'value' => "Paul",
            ),
            $name3 => array(
                'label' => $label3,
                'value' => "Monsieur",
            ),
        );
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * 
     * @param array $data
     * @dataProvider getCompleteDataForLabelsAndValuesExtraction
     */
    public function testExtractingLabelsAndValuesFromFieldsetHavingACheckboxElement($data)
    {
        $fieldset = new Fieldset('fs');
        $fieldset->add(new Text($name1 = 'nom', array('label' => $label1 = "Prénom")));
        $fieldset->add(new Text($name2 = 'prenom', array('label' => $label2 = "Prénom")));
        $fieldset->add(new Select($name3 = 'civ', array('label' => $label3 = "Civilité", 'value_options' => array('M'=>'Monsieur', 'Mme'=>'Madame'))));
        $fieldset->add(new Checkbox($name4 = 'agree', array('label' => $label4 = "D'accord?")));
        
        $actual = $this->form->getLabelsAndValues($fieldset, $data);
        $expected = array(
            $name1 => array(
                'label' => $label1,
                'value' => "Hochon",
            ),
            $name2 => array(
                'label' => $label2,
                'value' => "Paul",
            ),
            $name3 => array(
                'label' => $label3,
                'value' => "Monsieur",
            ),
            $name4 => array(
                'label' => $label4,
                'value' => "Oui",
            ),
        );
        $this->assertEquals($expected, $actual);
    }
}