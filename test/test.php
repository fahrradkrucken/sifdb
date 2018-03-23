<?php
if (!defined('DIRSEP')) define('DIRSEP', DIRECTORY_SEPARATOR);
require_once realpath(__DIR__ . '/../vendor/autoload.php');

$memory_used = memory_get_usage();

$original_str = 'Hey Nigga!';
$key = 'ololoshko';


print formatBytes(memory_get_usage() - $memory_used);

function var_get($v) {
    echo '<pre>';
    var_dump($v);
    echo '</pre>';
}

function formatBytes($bytes, $precision = 2) {
    $units = array("b", "kb", "mb", "gb", "tb");

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . " " . $units[$pow];
}

//print formatBytes(memory_get_peak_usage());