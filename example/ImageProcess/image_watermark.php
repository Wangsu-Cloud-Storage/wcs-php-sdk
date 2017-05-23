<?php

require_once __DIR__ . '/../common.php';
use Wcs\ImageProcess\ImageWatermark;

function print_help() {
    echo "Usage: php image_watermark.php [-h | --help] -b <bucketName> -f <fileName> [-l localFile]\n";
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

if (!isset($options['b']) || !isset($options['f']))  {
    print_help();
    exit(0);
}

$bucketName = $options['b'];
$fileName = $options['f'];
$localFile = $options['l'];

print("bucketName: \t$bucketName\n");
print("fileName: \t$fileName\n");
print("localFile: \t$localFile\n");

print("\n");


$mode = 2;
$client = new ImageWatermark($mode, "test");

//可选参数
//$client->width = 200;
//$client->height = 200;

print_r($client->exec($bucketName, $fileName, $localFile));

print("\n");