<?php
/*
* persistentStatus.php
*
* created by laihy 2017年5月4日
*/

require dirname(__FILE__).'/../autoload.php';

use wcs\WcsClient;

$options = $argv;
$persistentId = $options[1];

$client = new WcsClient();
$response = $client->persistentStatus($persistentId);

if ($response->isSuccess()) {
    var_dump($response->getData());
} else {
    var_dump($response->getMessage());
}