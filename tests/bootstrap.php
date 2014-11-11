<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Docker_Tests', __DIR__);

include_once('Docker/TestCase.php');



