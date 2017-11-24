<?php
/**
 * @author Stéphane Bouvry<stephane.bouvry@unicaen.fr>
 * @date: 17-11-24 11:36
 * @copyright Certic (c) 2017
 */

namespace OscarTest\Service;


use Oscar\Entity\ActivityDate;
use Oscar\Entity\DateType;
use Oscar\Service\NotificationService;
use OscarTest\Bootstrap;
use PHPUnit\Framework\TestCase;

class NotificationServiceTest extends TestCase
{
    function testGenerateForEnptyActivity(){
        /** @var NotificationService $serviceNotification */
        $serviceNotification = Bootstrap::getServiceManager('notification');

        $this->assertNotNull($serviceNotification);

        $milestone = new ActivityDate();
        $datetype = new DateType();
        $datetype->setRecursivity("30,20,10");
        $milestone->setType($datetype);


    }

}