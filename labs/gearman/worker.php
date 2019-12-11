<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 11/12/19
 * Time: 11:16
 */

$worker = new GearmanWorker();
$worker->addServer();
$worker->addFunction('foo', 'foo_function');
echo "Start worker FOO\n";

while($worker->work());


function foo_function(GearmanJob  $job){
    $workload = $job->workload();
    echo date('y-m-d H:i:s')." Call  foo_function with args : ";
    print_r($workload);
    echo "\n----\n";
}

