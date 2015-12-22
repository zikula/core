<?php

if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require_once __DIR__.'/../vendor/autoload.php';
} else {
    die('The test environment requires composer to install vendors, have you run composer?');
}

if (!class_exists('Symfony\Component\HttpFoundation\Request')) {
    die('The test environment requires Symfony2 HttpFoundation Component, have you run composer?');
}

if (!class_exists('Doctrine\ORM\EntityManager')) {
    die('The test environment requires Doctrine ORM, have you run composer?');
}
