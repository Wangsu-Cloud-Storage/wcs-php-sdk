<?php
require_once __DIR__ . '/../common.php';
use Wcs\Upload\Uploader;
use Wcs\Http\PutPolicy;
use Wcs\Config;

function print_help() {
    echo "Usage: php file_upload_notify.php [-h | --help] -b <bucketName> -f <fileKey> -l <localFile> -n <notifyUrl> -c <cmd> [-r <returnBody> -u <userParam>] [-v <userVars>] [-m <mimeType>]\n";
}
$opts = "hb:f:l:c:n:r:u:v:m:";
$longopts = array (
    'h',
    'help'
);

$options = getopt($opts, $longopts);
if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
}

if (!isset($options['b']) || !isset($options['f']) || !isset($options['l']) || !isset($options['c']) || !isset($options['n'])) {
    print_help();
    exit(0);
}

$bucketName = $options['b'];
$fileKey = $options['f'];
$localFile = $options['l'];
$notifyUrl = $options['n'];
$cmd = $options['c'];
$returnBody = (isset($options['r'])) ? $options['r'] : null;
$userParam = (isset($options['u'])) ? $options['u'] : null;
$userVars = (isset($options['v'])) ? $options['v'] : null;
$mimeType = (isset($options['m'])) ? $options['m'] : null;

print("bucket: \t$bucketName\n");
print("file: \t\t$fileKey\n");
print("localFile: \t$localFile\n");
print("notifyUrl: \t$notifyUrl\n");
print("operation: \t$cmd\n");
print("\n");

$pp = new PutPolicy();
$pp->overwrite = Config::WCS_OVERWRITE;
if ($fileKey == null || $fileKey === '') {
    $pp->scope = $bucketName;
} else {
    $pp->scope = $bucketName . ':' . $fileKey;
}
$pp->persistentNotifyUrl = $notifyUrl;
$pp->persistentOps = $cmd;
$pp->returnBody = $returnBody;
$token = $pp->get_token();

$client = new Uploader($token, $userParam, $userVars, $mimeType);

$res = $client->upload_return($localFile);
print_r($res->code." ".$res->respBody);

print("\n");
