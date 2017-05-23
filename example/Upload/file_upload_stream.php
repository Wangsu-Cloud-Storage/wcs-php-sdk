<?php
require_once __DIR__ . '/../common.php';
use Wcs\Upload\StreamUploader;
use Wcs\Http\PutPolicy;
use Wcs\Config;

$bucketName = '';
$fileKey = '';
$stream = '';

print("bucket: \t$bucketName\n");
print("file: \t\t$fileKey\n");
print("stream: \t$stream\n");
print("\n");

$pp = new PutPolicy();
$pp->overwrite = Config::WCS_OVERWRITE;
if ($fileKey == null || $fileKey == '') {
    $pp->scope = $bucketName;
} else {
    $pp->scope = $bucketName . ':' . $fileKey;
}
$token = $pp->get_token();

$client = new StreamUploader($token);
$res = $client->upload_return($stream);
print_r($res->code." ".$res->respBody);
print("\n");
