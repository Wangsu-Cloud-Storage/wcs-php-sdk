<?php

require_once __DIR__ . '/../common.php';
use Wcs\ImageProcess\ImageMogr;

function print_help() {
    echo "Usage: php image_mogr.php [-h | --help] -b <bucketName> -f <fileName> [-l localFile]\n";
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


$client = new ImageMogr();

$client->thumbnail = '!10p';

print_r($client->exec($bucketName, $fileName));

print("\n");