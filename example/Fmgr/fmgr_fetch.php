<?php

// 请先填写相关字段,$fops字段格式详见wcs api 文档
require_once __DIR__ . '/../common.php';
use Wcs\Fmgr\Fmgr;
use Wcs\Config;
use Wcs\Utils;
use Wcs\MgrAuth;

//可选参数
$notifyURL = '';
$force = 0;
$separate  = 0;

//fops参数
$fetchURL = Utils::url_safe_base64_encode('<input key>');
$bucket = Utils::url_safe_base64_encode('<input key>');
$key = Utils::url_safe_base64_encode('<input key>');
$prefix = Utils::url_safe_base64_encode('<input key>');
//$md5 = null;

$fops = 'fops=fetchURL/'.$fetchURL.'/bucket/'.$bucket.'/key/'.$key.'/prefix/'.$prefix.'&notifyURL='.Utils::url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;

$auth = new MgrAuth($ak, $sk);

$client = new Fmgr($auth, $notifyURL, $force, $separate);

$res = $client->fetch($fops);
print_r($res->code." ".$res->respBody);
print_r("\n");
