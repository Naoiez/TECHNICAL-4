<?php
$autoload = dirname(__DIR__).'/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
    $app = require dirname(__DIR__).'/bootstrap/app.php';
    $app->handleRequest(\Illuminate\Http\Request::capture());
    exit;
}
require dirname(__DIR__).'/fallback/app.php';
