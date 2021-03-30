<?php

require_once __DIR__ . '/../common.php';
use Wcs\PersistentFops\Fops;
use Wcs\Config;
use Wcs\MgrAuth;

$bucket = 'laihy-test';
$key = 'Wildlife.mp4';

//参数设置
$notifyURL = 'http://callback-test.wcs.biz.matocloud.com:8088/notifyUrl';
$force = 0;
$separate = 0;

$ak = Config::get('WCS_ACCESS_KEY');
$sk = Config::get('WCS_SECRET_KEY');
$auth = new MgrAuth($ak, $sk);

$fops = 'vframe/jpg/offset/10/w/1000/h/1000|saveas/bGFpaHktdGVzdDp2ZnJhbWUtdGVzdC0yNy5qcGc=';
$client = new Fops($auth, $bucket);

$res = $client->exec($fops, $key, $notifyURL);
print_r($res->code." ".$res->respBody);
print("\n");
