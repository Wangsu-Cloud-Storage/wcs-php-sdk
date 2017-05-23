<?php
require_once __DIR__ . '/../common.php';
use Wcs\Upload\ResumeUploader;
use Wcs\Http\PutPolicy;
use Wcs\Config;
use Wcs\Utils;

function print_help() {
    echo "Usage: php file_upload_resume.php [-h | --help] -b <bucketName> -f <fileKey> -l <localFile> [-u <userParam>] [-v <encodeUserVars>] [-m <mimeType>] \n";
}
$opts = "hb:f:l:u:v:m:";
$longopts = array (
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

$mimeType = (isset($options['m'])) ? $options['m'] : null;
$userParam = (isset($options['u'])) ? $options['u'] : null;
$encodeUserVars =  (isset($options['v'])) ?  Utils::url_safe_base64_encode($options['v']) : null;


print("bucket: \t$bucketName\n");
print("file: \t\t$fileKey\n");
print("localFile: \t$localFile\n");
print("\n");

$pp = new PutPolicy();
$pp->overwrite = Config::WCS_OVERWRITE;
if ($fileKey == null || $fileKey === '') {
    $pp->scope = $bucketName;
} else {
    $pp->scope = $bucketName . ':' . $fileKey;
}
$token = $pp->get_token();

$client = new ResumeUploader($token, $userParam, $encodeUserVars, $mimeType);
$res = $client->upload($localFile);
print_r($res->code." ".$res->respBody);
print("\n");
