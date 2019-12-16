<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                                                                                                    //
//                                             GEARMAN WORKER v0.1                                                    //
//          /home/bouvry/Projects/Unicaen/oscar/labs/gearman/worker.php                                                                                                          //
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$version=0.1;
$PATH=dirname(__DIR__);

oscarlog("OSCAR WORKER $version START ($PATH)");

$worker = new GearmanWorker();

// Par défaut Ajoute 127.0.0.1 (localhost)
$worker->addServer();
$worker->addFunction('test_foo', 'test_foo');
$worker->addFunction('test_bar', 'test_bar');

while($worker->work());



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function test_bar(GearmanJob $job){
    $params = json_decode($job->workload());
    oscarlog(sprintf("Execution de 'test_bar' avec '%s'", $job->workload()));
    try {
        if( !property_exists($params, 'msg') ){
            throw new Exception("PAS DE MESSAGE");
        }
    } catch (Exception $e) {
        echo "[ERR] " . $e->getMessage() ."\n";
    }
}

function test_foo(GearmanJob $job){
    $params = json_decode($job->workload());

    oscarlog(sprintf("Execution de 'test_foo' avec '%s'", $job->workload()));

    if( !property_exists($params, 'msg') ){
        oscarlog("Pas de MSG reçu", 'ERROR');
        $job->sendFail();
        return;
    }

    if( $params->msg == 'exit' ){
        $job->sendException("Sortie brutale");
        oscarlog("Test EXIT 0x0a", 'ERROR');
        exit(0x0a);
    }

    if( $params->msg == 'exception' ){
        oscarlog("Test EXCEPTION non catchée", 'ERROR');
        throw new Exception("Test EXCEPTION from service");
    }

    oscarlog("Msg reçu : " . $params->msg);
}


function oscarlog($msg, $mode='LOG'){
    if( $mode == 'LOG' )
        fwrite(STDOUT, sprintf("[%s worker log] %s\n", date('Y-m-d H:i:s'), $msg));
    else
        fwrite(STDERR, sprintf("[%s worker err] %s\n", date('Y-m-d H:i:s'), $msg));
}