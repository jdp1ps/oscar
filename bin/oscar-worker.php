<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///
///
///  OSCAR WORKER
///
///
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///
/// Usage : Voir documentation
/// Surveiller :
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Chemin "simplifié"
chdir(dirname(__DIR__));

// Autoload & Co
require __DIR__.'/../vendor/autoload.php';

// Configuration
$conf = require 'config/application.config.php';

// App
$app = Zend\Mvc\Application::init(require 'config/application.config.php');

/** @var \Oscar\Service\OscarConfigurationService $oscarConfigurationService */
$oscarConfigurationService = $app->getServiceManager()->get(\Oscar\Service\OscarConfigurationService::class);

// Worker
$worker = new GearmanWorker();
$worker->addServer($oscarConfigurationService->getGearmanHost());
$worker->addFunction('indexPerson', 'oscarJob_indexPerson');
$worker->addFunction('personSearchUpdate', 'oscarJob_indexPerson');
$worker->addFunction('indexActivity', 'oscarJob_indexActivity');
$worker->addFunction('activitySearchUpdate', 'oscarJob_indexActivity');
$worker->addFunction('notificationActivityPerson', 'oscarJob_notificationActivityPerson');
$worker->addFunction('purgeNotificationsPersonActivity', 'oscarJob_purgeNotificationsPersonActivity');
$worker->addFunction('hello', 'oscarJob_hello');

// Affiche dans le journalctl -u oscarworker.service -f
echo "OSCAR WORKER STARTED\n";

while($worker->work());

function getServiceManager(){
    global $app;
    return $app->getServiceManager();
}

function oscarJob_indexActivity(GearmanJob $job){
    $params = json_decode($job->workload());



    try {
        if( !property_exists($params, 'activityid') ){
            throw new Exception("Paramètres manquant ID");
        }
        /** @var \Oscar\Service\ProjectGrantService $activityService */
        $activityService = getServiceManager()->get(\Oscar\Service\ProjectGrantService::class);


        $activityid = $params->activityid;
        $activityService->getLoggerService()->info("[GEARMAN] Indexation de Activity $activityid");
        $activity = $activityService->getActivityById($activityid);
        echo date('y-m-d H:i:s')." Rebuid Index [$activityid] $activity\n";
        $activityService->searchUpdate($activity);

    } catch (Exception $e) {
        $activityService->getLoggerService()->info("[GEARMAN] Error " . $e->getMessage());
        echo "[ERR] " . $e->getMessage() ."\n";
    }
}

function oscarJob_indexPerson(GearmanJob $job){
    $params = json_decode($job->workload());

    try {
        if( !property_exists($params, 'personid') ){
            throw new Exception("Paramètres manquant ID");
        }
        /** @var \Oscar\Service\PersonService $personService */
        $personService = getServiceManager()->get(\Oscar\Service\PersonService::class);

        $personId = $params->personid;

        getServiceManager()->get('Logger')->info("[GEARMAN] Indexation de Person $personId");
        $person = $personService->getPerson($personId);
        echo date('y-m-d H:i:s')." Rebuid Index [$personId] $person\n";
        $personService->getSearchEngineStrategy()->update($person);

    } catch (Exception $e) {
        getServiceManager()->get('Logger')->info("[GEARMAN] Error " . $e->getMessage());
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

        getServiceManager()->get('Logger')->info("[GEARMAN] Notification Person:$personId - Activity:$activityId");

        echo date('y-m-d H:i:s')." Notification Activity Person [$activityId, $personId] $activity >  $person\n";
        $notificationService->generateNotificationsForActivity($activity, $person);

    } catch (Exception $e) {
        echo "[ERR] " . $e->getMessage() ."\n";
        getServiceManager()->get('Logger')->info("[GEARMAN] Error " . $e->getMessage());
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

        getServiceManager()->get('Logger')->info("[GEARMAN] Purge notification Person:$personId Activity:$activityId");

        echo date('y-m-d H:i:s')." PURGE Notification Activity Person [$activityId, $personId] $activity >  $person\n";
        $notificationService->purgeNotificationsPersonActivity($activity, $person);

    } catch (Exception $e) {
        getServiceManager()->get('Logger')->info("[GEARMAN] Error " . $e->getMessage());
        echo "[ERR] " . $e->getMessage() ."\n";
    }
}

function oscarJob_hello(GearmanJob $job){
    $params = json_decode($job->workload());

    try {


        /** @var \Oscar\Service\ProjectGrantService $projectGrantService */
        $projectGrantService = getServiceManager()->get(\Oscar\Service\ProjectGrantService::class);

        /** @var \Oscar\Service\PersonService $personService */
        $personService = getServiceManager()->get(\Oscar\Service\PersonService::class);

        /** @var \Oscar\Service\NotificationService $notificationService */
        $notificationService = getServiceManager()->get(\Oscar\Service\NotificationService::class);

        /** @var \Monolog\Logger $loggerService */
        $loggerService = getServiceManager()->get('Logger');

        echo "Hello with " . print_r($params) . "\n";

        // Envoi d'un log vers OSCAR
        $loggerService->info("[GEARMAN] a bien reçu la tâche HELLO");

        $job->sendComplete("TRAITEMENT RÉUSSI");

    } catch (Exception $e) {
        $job->sendException("[ERR] HELLO FAIL : " . $e->getMessage());
    }
}