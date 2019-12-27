<?php
require_once __DIR__ . '/../common.php';
use Wcs\SrcManage\FileManager;
use Wcs\Config;
use Wcs\MgrAuth;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;

$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);

$res = $client->bucketStat('bucketName', 'startDate', 'endDate');
print_r($res->code." ".$res->respBody);
print("\n");
