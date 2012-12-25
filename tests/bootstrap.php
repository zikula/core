<?php

use Composer\Autoload\ClassLoader;

error_reporting(E_ALL | E_STRICT);
$loader = require_once __DIR__.'/../src/vendor/autoload.php';
$loader->add('Tests_', __DIR__.'/lib');


