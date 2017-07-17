#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;

require_once 'src/vendor/autoload.php';
require_once 'build/BuildPackageCommand.php';
require_once 'build/FixAutoloaderCommand.php';
require_once 'build/GenerateVendorDocCommand.php';
require_once 'build/LessCommand.php';
require_once 'build/PurgeVendorsCommand.php';

$application = new Application();
$application->add(new BuildPackageCommand());
$application->add(new PurgeVendorsCommand());
$application->add(new FixAutoloaderCommand());
$application->add(new GenerateVendorDocCommand());
$application->add(new LessCommand());
$application->run();
