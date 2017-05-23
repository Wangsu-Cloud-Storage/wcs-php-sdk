<?php

// 请先填写相关字段,$fops字段格式详见wcs api 文档
require_once __DIR__ . '/../common.php';
use Wcs\Fmgr\Fmgr;

function print_help() {
    echo "Usage: php fmgr_status.php [-h | --help] -p <persistentId>\n";
}
$opts = "hp:";
$longopts = array (
    'h',
    'help'
);
$options = getopt($opts, $longopts);
if (isset($options['h']) || isset($options['help'])) {
    print_help();
    exit(0);
}

if (!isset($options['p']))  {
    print_help();
    exit(0);
}

$persistentId = $options['p'];
print("persistentId: \t$persistentId\n");
print("\n");

$res = Fmgr::status($persistentId);
print_r($res->code." ".$res->respBody);
print_r("\n");
