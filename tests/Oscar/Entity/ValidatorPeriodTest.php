<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 18/04/2018
 * Time: 13:39
 */

namespace Oscar\Entity;

use Oscar\Exception\OscarException;
use PHPUnit\Framework\TestCase;

class ValidatorPeriodTest extends TestCase
{

    /**
     * @return ValidationPeriod
     */
    protected function getValidatorPeriod(){
        $validatorPeriod = new ValidationPeriod();

        $validateurSB = new Person();
        $validateurSB->setFirstname("Stéphane")->setLastname("Bouvry");

        $validateurAD = new Person();
        $validateurAD->setFirstname("Arnaud")->setLastname("Daret");

        $validateurCT = new Person();
        $validateurCT->setFirstname("Christophe")->setLastname("Turbout");

        $validateurAL = new Person();
        $validateurAL->setFirstname("Anne")->setLastname("Lecarpentier");

        $validateurBB = new Person();
        $validateurBB->setFirstname("Bruno")->setLastname("Bernard");

        $validatorPeriod
            ->addValidatorPrj($validateurAD)
            ->addValidatorSci($validateurCT)
            ->addValidatorAdm($validateurAL)->addValidatorAdm($validateurBB);

        return $validatorPeriod;
    }


    public function testValidateursActivity(){

        $validationPeriod = $this->getValidatorPeriod();

        $validationPeriod->setStatus(ValidationPeriod::STATUS_STEP1)
            ->setObject(ValidationPeriod::OBJECT_ACTIVITY)
            ->setObjectGroup(ValidationPeriod::GROUP_WORKPACKAGE);

        $this->assertEquals(1, count($validationPeriod->getValidatorsPrj()));
        $this->assertEquals(1, count($validationPeriod->getValidatorsSci()));
        $this->assertEquals(2, count($validationPeriod->getValidatorsAdm()));


        // Validation PROJET
        $validationPeriod->setStatus(ValidationPeriod::STATUS_STEP1);
        $this->assertTrue($validationPeriod->isActivityValidation());
        $nextValidateur = $validationPeriod->getCurrentValidators();
        $this->assertEquals(1, count($nextValidateur));
        foreach ($nextValidateur as $v){
            $this->assertEquals("Arnaud Daret", (string)$v);
        }

        // Validation SCIENTIFIQUE
        $validationPeriod->setStatus(ValidationPeriod::STATUS_STEP2);
        $this->assertTrue($validationPeriod->isActivityValidation());
        $nextValidateur = $validationPeriod->getCurrentValidators();
        $this->assertEquals(1, count($nextValidateur));
        $this->assertEquals("Christophe Turbout", (string)$nextValidateur[0]);

        // Validation ADMINISTRATIF
        $validationPeriod->setStatus(ValidationPeriod::STATUS_STEP3);
        $this->assertTrue($validationPeriod->isActivityValidation());
        $nextValidateur = $validationPeriod->getCurrentValidators();
        $this->assertEquals(2, count($nextValidateur));
        $this->assertEquals("Anne Lecarpentier", (string)$nextValidateur[0]);
        $this->assertEquals("Bruno Bernard", (string)$nextValidateur[1]);
    }
}