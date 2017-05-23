<?php
require_once __DIR__ . '/../common.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

function print_help() {
    echo "Usage: php file_list.php [-h | --help] -b <bucketName> [-l <limit>] [-p <prefix>] [-m <mode>] [-M <marker>]\n";
}
$opts = "hb:l:p:m:M:";
$longopts = array (
    'h',
    'help',
);

$options = getopt($opts, $longopts);
if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
}

if (!isset($options['b']))  {
    print_help();
    exit(0);
}

$bucketName = $options['b'];
$limit = isset($options['l']) ? $options['l'] : 1000;
$prefix = isset($options['p']) ? $options['p'] : null;
$mode = isset($options['m']) ? $options['m'] : null;
$marker= isset($options['M']) ? $options['M'] : null;

print("bucket: \t$bucketName\n");
print("limit: \t$limit\n");
print("prefix: \t$prefix\n");
print("mode: \t$mode\n");
print("marker: \t$marker\n");

print("\n");

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;

$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);
$res = $client->bucketList($bucketName, $limit, $prefix, $mode, $marker);
print_r($res->code." ".$res->respBody);
print("\n");
