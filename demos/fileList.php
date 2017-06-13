<?php
/*
 * fileList.php
 *
 * created by laihy 2017年4月28日
 */


require dirname(__FILE__).'/../autoload.php';

use wcs\WcsClient;

$options = $argv;
$bucket = $options[1];

$client = new WcsClient();
$response = $client->fileList($bucket);

if ($response->isSuccess()) {
    var_dump($response->getData());
} else {
    var_dump($response->getMessage());
}