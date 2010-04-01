<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'FileSystemAllTests::main');
}

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/FileSystem_ErrorTest.php';
require_once dirname(__FILE__) . '/Configuration/FileSystem_Configuration_FtpTest.php';
require_once dirname(__FILE__) . '/Configuration/FileSystem_Configuration_SftpTest.php';
require_once dirname(__FILE__) . '/Configuration/FileSystem_Configuration_LocalTest.php';
require_once dirname(__FILE__) . '/FileSystem_FtpTest.php';
require_once dirname(__FILE__) . '/FileSystem_SftpTest.php';
require_once dirname(__FILE__) . '/FileSystem_LocalTest.php';
require_once dirname(__FILE__) . '/FileSystem_DriverTest.php';


class I18nAllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('FileSystem Tests');
        $suite->addTestSuite('FileSystem_ErrorTest');
        $suite->addTestSuite('FileSystem_Configuration_FtpTest');
        $suite->addTestSuite('FileSystem_Configuration_SftpTest');
        $suite->addTestSuite('FileSystem_Configuration_LocalTest');
        $suite->addTestSuite('FileSystem_FtpTest');
        $suite->addTestSuite('FileSystem_SftpTest');
        $suite->addTestSuite('FileSystem_LocalTest');
        $suite->addTestSuite('FileSystem_DriverTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'FileSystemTests::main') {
    I18nAllTests::main();
}