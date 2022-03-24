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

$oscarCmd = '/usr/bin/php bin/oscar.php console ';

// Autoload & Co
require __DIR__ . '/../vendor/autoload.php';

// Configuration
$conf = require 'config/application.config.php';

// App
$app = Zend\Mvc\Application::init(require 'config/application.config.php');

/** @var \Oscar\Service\OscarConfigurationService $oscarConfigurationService */
$oscarConfigurationService = $app->getServiceManager()->get(\Oscar\Service\OscarConfigurationService::class);

// Worker
$worker = new GearmanWorker();
$worker->addServer($oscarConfigurationService->getGearmanHost());

$worker->addFunction('updateIndexActivity', 'oscarJob_updateIndexActivity');
$worker->addFunction('updateIndexPerson', 'oscarJob_updateIndexPerson');
$worker->addFunction('updateIndexOrganization', 'oscarJob_updateIndexOrganization');
$worker->addFunction('updateNotificationsActivity', 'oscarJob_updateNotificationsActivity');

$worker->addFunction('hello', 'oscarJob_hello');

// Affiche dans le journalctl -u oscarworker.service -f
$execDev = "2";
echo "###################################################################\n";
echo "# OSCAR WORKER STARTED " . \Oscar\OscarVersion::getBuild() . " SPARTAN\n";
echo "# working directory : '" . __DIR__ . "'\n";
echo "###################################################################\n";

while ($worker->work()) {
    ;
}

function getServiceManager()
{
    global $app;
    return $app->getServiceManager();
}

function oscarJob_updateIndexActivity(GearmanJob $job)
{
    global $oscarCmd;
    $params = json_decode($job->workload());
    try {
        if (!property_exists($params, 'activityid')) {
            throw new Exception("Paramètres manquant 'activityid'");
        }
        $activityid = $params->activityid;
        $cmd = $oscarCmd . ' indexactivity \'{"activityid":' . $params->activityid . '}\'';
        echo "[worker] exec $cmd\n";
        exec($cmd);
    } catch (Exception $e) {
        echo "[ERR] " . $e->getMessage() . "\n";
    }
}

function oscarJob_updateIndexPerson(GearmanJob $job)
{
    global $oscarCmd;
    $params = json_decode($job->workload());
    try {
        if (!property_exists($params, 'personid')) {
            throw new Exception("Paramètres manquant 'personid'");
        }
        $personid = $params->personid;
        $cmd = $oscarCmd . ' indexperson \'{"personid":' . $personid . '}\'';
        echo "[worker] exec $cmd\n";
        exec($cmd);
    } catch (Exception $e) {
        echo "[ERR] " . $e->getMessage() . "\n";
    }
}

function oscarJob_updateIndexOrganization(GearmanJob $job)
{
    global $oscarCmd;
    $params = json_decode($job->workload());
    try {
        if (!property_exists($params, 'organizationid')) {
            throw new Exception("Paramètres manquant 'organizationid'");
        }
        $organizationid = $params->organizationid;
        $cmd = $oscarCmd . ' indexorganization \'{"organizationid":' . $organizationid . '}\'';
        echo "[worker] exec $cmd\n";
        exec($cmd);
    } catch (Exception $e) {
        echo "[ERR] " . $e->getMessage() . "\n";
    }
}

function oscarJob_updateNotificationsActivity(GearmanJob $job)
{
    global $oscarCmd;

    $params = json_decode($job->workload());

    try {
        if (!property_exists($params, 'activityid')) {
            throw new Exception("Paramètres manquant 'activityid'");
        }
        $activityid = $params->activityid;
        $cmd = $oscarCmd . ' notificationsactivity \'{"activityid":' . $params->activityid . '}\'';
        echo "[worker] exec $cmd\n";
        exec($cmd);
    } catch (Exception $e) {
        echo "[ERR] " . $e->getMessage() . "\n";
    }
}

function oscarJob_hello(GearmanJob $job)
{
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

        echo "[worker] Hello with " . print_r($params) . "\n";

        // Envoi d'un log vers OSCAR
        getServiceManager()->get('Logger')->info(" > [gearman:call] TEST OK]");

        $job->sendComplete("TRAITEMENT RÉUSSI");
    } catch (Exception $e) {
        $job->sendException("[ERR] HELLO FAIL : " . $e->getMessage());
    }
}