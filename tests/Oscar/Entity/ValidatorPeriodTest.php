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
        $validateurAL->setFirstname("Anne")->setLastname("Lecartentier");

        $validatorPeriod
            ->addValidatorPrj($validateurAD)
            ->addValidatorSci($validateurCT)
            ->addValidatorAdm($validateurAL);

        return $validatorPeriod;
    }


    public function testValidateursActivity(){

        $validationPeriod = $this->getValidatorPeriod();

        $validationPeriod->setStatus(ValidationPeriod::STATUS_STEP1)->setObject(ValidationPeriod::OBJECT_ACTIVITY);

        $this->assertEquals(1, count($validationPeriod->getValidatorsPrj()));
        $this->assertEquals(1, count($validationPeriod->getValidatorsSci()));
        $this->assertEquals(1, count($validationPeriod->getValidatorsAdm()));

    }
}