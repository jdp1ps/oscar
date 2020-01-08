<?php
/**
 * Created by PhpStorm.
 * User: bouvry
 * Date: 07/01/20
 * Time: 14:19
 */
$additionalHeaders = '';
$payloadName = '';
$username = 'oscar-test';
$password = 'ùLDWbàaf95OG3Àê5ÜuÀâééÈÙZbBJlqê5';
$host = 'http://localhost:8080/api/persons';
$ch = curl_init($host);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadName);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$return = curl_exec($ch);
echo $return;
curl_close($ch);