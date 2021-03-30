<?php
require_once __DIR__ . '/../common.php';
use Wcs\SrcManage\FileManager;
use Wcs\Config;
use Wcs\MgrAuth;

$ak = Config::get('WCS_ACCESS_KEY');
$sk = Config::get('WCS_SECRET_KEY');

$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);

$res = $client->bucketsList();
print_r($res->code." ".$res->respBody);
print("\n");
