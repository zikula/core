<?php
namespace Zikula\Tests\Common\ServiceManager;

if (!defined('\PHPUnit_MAIN_METHOD')) {
    define('\PHPUnit_MAIN_METHOD', '\Zikula\Tests\Common\ServiceManager::main');
}

require_once __DIR__ . '/../../../../../bootstrap.php';

class AllTests
{
    public static function main()
    {
        \PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new \PHPUnit_Framework_TestSuite('Zikula\Tests\Common\ServiceManager Tests');
        $suite->addTestSuite('Zikula\Tests\Common\ServiceManager\ServiceTest');
        $suite->addTestSuite('Zikula\Tests\Common\ServiceManager\DefinitionTest');
        $suite->addTestSuite('Zikula\Tests\Common\ServiceManager\ServiceManagerTest');
        return $suite;
    }
}

if (\PHPUnit_MAIN_METHOD == '\Zikula\Tests\Common\ServiceManager::main') {
    AllTests::main();
}