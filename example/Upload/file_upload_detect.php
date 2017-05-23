<?php
require_once __DIR__ . '/../common.php';
use Wcs\Upload\Uploader;
use Wcs\Http\PutPolicy;
use Wcs\Config;

function print_help() {
    echo "Usage: php file_upload_return.php [-h | --help] -b <bucketName> -f <fileKey> -l <localFile> \n";
}
$opts = "hb:f:l:";
$longopts = array (
    'h',
    'help'
);

$options = getopt($opts, $longopts);
if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
}

if (!isset($options['b']) || !isset($options['f']) || !isset($options['l'])) {
    print_help();
    exit(0);
}

$bucketName = $options['b'];
$fileKey = $options['f'];
$localFile = $options['l'];
$contentdetect = 'imagePorn';
$detectNotifyURL = '';
$detectNotifyRule = 'all';

print("bucket: \t$bucketName\n");
print("file: \t\t$fileKey\n");
print("localFile: \t$localFile\n");
print("contentdetect: \t$contentdetect\n");
print("\n");

$pp = new PutPolicy();
$pp->overwrite = Config::WCS_OVERWRITE;
if ($fileKey == null || $fileKey == '') {
    $pp->scope = $bucketName;
} else {
    $pp->scope = $bucketName . ':' . $fileKey;
}
$pp->deadline = '1483027200000';
$pp->contentDetect = $contentdetect;
if ($detectNotifyURL != null || $detectNotifyURL != '') {
    $pp->detectNotifyURL = $detectNotifyURL;
}
if ($detectNotifyRule != null || $detectNotifyRule != '') {
    $pp->detectNotifyRule = $detectNotifyRule;
}
$token = $pp->get_token();

$client = new Uploader($token);
print_r($client->upload_return($localFile));
print("\n");
