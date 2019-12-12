<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 11/12/19
 * Time: 15:31
 */

require __DIR__.'/../../../../../../vendor/autoload.php';

$conf = require __DIR__.'/../../../../../../config/application.config.php';
$app = Zend\Mvc\Application::init($conf);

$worker = new GearmanWorker();
$worker->addServer();
$worker->addFunction('indexPerson', 'oscarJob_indexPerson');
$worker->addFunction('notificationActivityPerson', 'oscarJob_notificationActivityPerson');
$worker->addFunction('purgeNotificationsPersonActivity', 'oscarJob_purgeNotificationsPersonActivity');

echo "OSCAR WORKER STARTED " . \Oscar\OscarVersion::getBuild() ."\n";

while($worker->work());

function getServiceManager(){
    global $app;
    return $app->getServiceManager();
}

function oscarJob_indexPerson(GearmanJob $job){
    $params = json_decode($job->workload());

    try {
        if( !property_exists($params, 'id') ){
            throw new Exception("Paramètres manquant ID");
        }
        /** @var \Oscar\Service\PersonService $personService */
        $personService = getServiceManager()->get(\Oscar\Service\PersonService::class);

        $personId = $params->id;
        $person = $personService->getPerson($personId);
        echo date('y-m-d H:i:s')." Rebuid Index [$personId] $person\n";
        $personService->getSearchEngineStrategy()->update($person);

    } catch (Exception $e) {
        echo "[ERR] " . $e->getMessage() ."\n";
    }
}

function oscarJob_notificationActivityPerson(GearmanJob $job){
    $params = json_decode($job->workload());

    try {
        if( !property_exists($params, 'personid') || !property_exists($params, 'activityid') ){
            throw new Exception("Paramètres manquant ID");
        }

        /** @var \Oscar\Service\ProjectGrantService $projectGrantService */
        $projectGrantService = getServiceManager()->get(\Oscar\Service\ProjectGrantService::class);

        /** @var \Oscar\Service\PersonService $personService */
        $personService = getServiceManager()->get(\Oscar\Service\PersonService::class);

        /** @var \Oscar\Service\NotificationService $notificationService */
        $notificationService = getServiceManager()->get(\Oscar\Service\NotificationService::class);

        $personId = $params->personid;
        $person = $personService->getPerson($personId);

        $activityId = $params->activityid;
        $activity = $projectGrantService->getActivityById($activityId);

        echo date('y-m-d H:i:s')." Notification Activity Person [$activityId, $personId] $activity >  $person\n";
        $notificationService->generateNotificationsForActivity($activity, $person);

    } catch (Exception $e) {
        echo "[ERR] " . $e->getMessage() ."\n";
    }
}


function oscarJob_purgeNotificationsPersonActivity(GearmanJob $job){
    $params = json_decode($job->workload());

    try {
        if( !property_exists($params, 'personid') || !property_exists($params, 'activityid') ){
            throw new Exception("Paramètres manquant ID");
        }

        /** @var \Oscar\Service\ProjectGrantService $projectGrantService */
        $projectGrantService = getServiceManager()->get(\Oscar\Service\ProjectGrantService::class);

        /** @var \Oscar\Service\PersonService $personService */
        $personService = getServiceManager()->get(\Oscar\Service\PersonService::class);

        /** @var \Oscar\Service\NotificationService $notificationService */
        $notificationService = getServiceManager()->get(\Oscar\Service\NotificationService::class);

        $personId = $params->personid;
        $person = $personService->getPerson($personId);

        $activityId = $params->activityid;
        $activity = $projectGrantService->getActivityById($activityId);

        echo date('y-m-d H:i:s')." PURGE Notification Activity Person [$activityId, $personId] $activity >  $person\n";
        $notificationService->purgeNotificationsPersonActivity($activity, $person);

    } catch (Exception $e) {
        echo "[ERR] " . $e->getMessage() ."\n";
    }
}