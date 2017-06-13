<?php
/*
* avBriefInfo.php
*
* created by laihy 2017年4月28日
*/

require dirname(__FILE__).'/../autoload.php';

use wcs\WcsClient;

$options = $argv;
$bucket = $options[1];
$key = $options[2];

$client = new WcsClient();
$response = $client->avBriefInfo($bucket, $key);

if ($response->isSuccess()) {
    var_dump($response->getData());
} else {
    var_dump($response->getMessage());
}