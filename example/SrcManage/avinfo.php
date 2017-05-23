<?php
require_once __DIR__ . '/../common.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

function print_help() {
    echo "Usage: php avinfo.php [-h | --help] -H <host> -k <key>\n";
}
$opts = "hk:H:";
$longopts = array (
    'h',
    'help'
);

$options = getopt($opts, $longopts);
if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
}

if (!isset($options['k']) || !isset($options['H']))  {
    print_help();
    exit(0);
}

$key = $options['k'];
$host = $options['H'];

print("fileKey: \t$key\n");
print("host:: \t$key\n");

print("\n");

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;

$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);

$res = $client->avInfo($host, $key);
print_r($res->code." ".$res->respBody);
print("\n");
