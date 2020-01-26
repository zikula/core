#!/usr/bin/env php
<?php

declare(strict_types=1);

use Symfony\Component\Console\Application;

require_once 'vendor/autoload.php';
require_once 'build/BuildPackageCommand.php';
require_once 'build/GenerateVendorDocCommand.php';
require_once 'build/PurgeVendorsCommand.php';

$application = new Application();
$application->add(new BuildPackageCommand());
$application->add(new PurgeVendorsCommand());
$application->add(new GenerateVendorDocCommand());
$application->run();
