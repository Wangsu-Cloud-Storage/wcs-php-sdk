<?php
require_once __DIR__ . '/../common.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

function print_help() {
    echo "Usage: php file_copy.php [-h | --help] --bs <bucketSrc> --ks <keyStr> --bd <bucketDst> --kd <keyDst>\n";
}
$opts = "h";
$longopts = array (
    'help',
    'bs:',
    'ks:',
    'bd:',
    'kd:'
);

$options = getopt($opts, $longopts);
if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
}

if (!isset($options['bs']) || !isset($options['ks']) || !isset($options['bd']) || !isset($options['kd']))  {
    print_help();
    exit(0);
}

$bucketSrc = $options['bs'];
$bucketDst = $options['bd'];
$keySrc = $options['ks'];
$keyDst = $options['kd'];

print("bucketSrc: \t$bucketSrc\n");
print("keySrc: \t$keySrc\n");
print("bucketDst: \t$bucketDst\n");
print("keyDst: \t$keyDst\n");

print("\n");

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;

$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);

$res = $client->copy($bucketSrc, $keySrc, $bucketDst, $keyDst);
print_r($res->code." ".$res->respBody);
print("\n");
