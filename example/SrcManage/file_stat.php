<?php
require_once __DIR__ . '/../common.php';
use Wcs\SrcManage\FileManager;
use Wcs\Config;
use Wcs\MgrAuth;

function print_help() {
    echo "Usage: php file_stat.php [-h | --help] -b <bucketName> -f <fileKey>\n";
}
$opts = "hb:f:";
$longopts = array (
    'h',
    'help'
);

$options = getopt($opts, $longopts);
if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
}

if (!isset($options['b']) || !isset($options['f']))  {
    print_help();
    exit(0);
}

$bucketName = $options['b'];
$fileKey = $options['f'];

print("bucket: \t$bucketName\n");
print("fileKey: \t$fileKey\n");

print("\n");

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;

$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);

$res = $client->stat($bucketName, $fileKey);
print_r($res->code." ".$res->respBody);
print("\n");
