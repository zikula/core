<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'FileSystemAllTests::main');
}

require_once dirname(__FILE__) . '/../../../../bootstrap.php';


class I18nAllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('FileSystem Tests');
        $suite->addTestSuite('Zikula_FileSystem_ErrorTest');
        $suite->addTestSuite('Zikula_FileSystem_Configuration_FtpTest');
        $suite->addTestSuite('Zikula_FileSystem_Configuration_SftpTest');
        $suite->addTestSuite('Zikula_FileSystem_Configuration_LocalTest');
        $suite->addTestSuite('Zikula_FileSystem_FtpTest');
        $suite->addTestSuite('Zikula_FileSystem_SftpTest');
        $suite->addTestSuite('Zikula_FileSystem_LocalTest');
        $suite->addTestSuite('Zikula_FileSystem_AbstractDriverTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'FileSystemTests::main') {
    I18nAllTests::main();
}