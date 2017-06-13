<?php
/*
* deleteM3u8.php
*
* created by laihy 2017年5月3日
*/

require dirname(__FILE__).'/../autoload.php';

use wcs\WcsClient;

$items = [
    [
        'bucket'        => 'laihy-test',
        'key'           => 'Wildlife.m3u8',
        'isDeleteTs'    => 1,
    ]
];
$notifyUrl = 'http://callback.baidu.com/m3u8';

$client = new WcsClient();
$response = $client->deleteM3u8($items, $notifyUrl);

if ($response->isSuccess()) {
    echo $response->getCode().PHP_EOL;
    var_dump($response->getData());
} else {
    var_dump($response->getMessage());
    echo PHP_EOL;
    var_dump($response->getHeader());
}