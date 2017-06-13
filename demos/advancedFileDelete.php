<?php
/*
* advancedFileDelete.php
*
* created by laihy 2017年5月4日
*/

require dirname(__FILE__).'/../autoload.php';

use wcs\WcsClient;

$items = [
    [
        'bucket'  => 'laihy-test',
        'key'     => 'sixxx.jpg',
    ],
    [
        'bucket'  => 'laihy-test',
        'key'     => 'jzm.jpg',
    ],
];

$client = new WcsClient();
$response = $client->advancedFileDelete($items);

if ($response->isSuccess()) {
    echo $response->getCode().PHP_EOL;
    var_dump($response->getData());
} else {
    var_dump($response->getMessage());
    echo PHP_EOL;
    var_dump($response->getHeader());
}