<?php
/**
 * Created by PhpStorm.
 * User: jacksay
 * Date: 11/12/19
 * Time: 11:18
 */
$client= new GearmanClient();
$client->addServer();
echo $client->doBackground('foo', json_encode(['foo' => 'bar']) );