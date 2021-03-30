<?php

// 请先填写相关字段,$fops字段格式详见wcs api 文档
require_once __DIR__ . '/../common.php';
use Wcs\Fmgr\Fmgr;
use Wcs\Config;
use Wcs\MgrAuth;
use Wcs\Utils;


//可选参数
$notifyURL = '';
$force = 0;
$separate  = 0;

//fops参数
$bucket = Utils::url_safe_base64_encode('<input key>');
$keys = Utils::url_safe_base64_encode('<input key1>').'|'.Utils::url_safe_base64_encode('<input key2>');
$keyList = Utils::url_safe_base64_encode('<input keylist>');
$targetSource = Utils::url_safe_base64_encode('<bucket:key>');


// $keys同$keyList同时存在时，$keyList无效
if ($keys) {
    $fops = 'fops=bucket/'.$bucket.'/keys/'.$keys.'/keylist/'.$keyList.'/saveas/'.$targetSource;
}

$ak = Config::get('WCS_ACCESS_KEY');
$sk = Config::get('WCS_SECRET_KEY');

$auth = new MgrAuth($ak, $sk);

$client = new Fmgr($auth, $notifyURL, $force, $separate);
$res = $client->compress($fops);
print_r($res->code." ".$res->respBody);
print_r("\n");
