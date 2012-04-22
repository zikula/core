<?php
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require_once __DIR__.'/../vendor/autoload.php';
} else {
    die('The test environment requires composer to install vendors, have you run composer?');
}

if (!class_exists('Symfony\Component\EventDispatcher\Event')) {
    die('The test environment requires Symfony2 Event Dispatcher Component, have you run composer?');
}

if (!class_exists('Symfony\Component\DependencyInjection\Container')) {
    die('The test environment requires Symfony2 Dependency Injection Component, have you run composer?');
}
