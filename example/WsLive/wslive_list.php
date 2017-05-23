<?php
require_once __DIR__ . '/../common.php';
use Wcs\Wslive\WsLive;
use Wcs\MgrAuth;
use Wcs\Config;

function print_help() {
    echo "Usage: php wslive_list.php [-h | --help] -c <channelname> -s <startTime> -e <endTime> -b <bucket> [-S <start>] [-L <limit>]\n";
}
$opts = "hc:s:e:b:S:L:";
$longopts = array (
    'h',
    'help',
);

$options = getopt($opts, $longopts);
if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
}

if (!isset($options['c'])||!isset($options['s'])||!isset($options['e'])||!isset($options['b']))  {
    print_help();
    exit(0);
}

$channelname = $options['c'];
$startTime = $options['s'];
$endTime = $options['e'];
$bucket = $options['b'];
$start= isset($options['S']) ? $options['S'] : null;
$limit = isset($options['L']) ? $options['L'] : null;

print("channelname: \t$channelname\n");
print("startTime: \t$startTime\n");
print("endTime: \t$endTime\n");
print("bucket : \t$bucket \n");
print("start : \t$start \n");
print("limit: \t$limit\n");

print("\n");

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;

$auth = new MgrAuth($ak, $sk);

$client = new WsLive($auth);

$res = $client->wslive_list($channelname, $startTime, $endTime, $bucket, $start, $limit);
print_r($res->code." ".$res->respBody);

print("\n");
