<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once dirname(__FILE__) . '/bootstrap.php';
require_once dirname(__FILE__) . '/lib/Tests/i18n/AllTests.php';
require_once dirname(__FILE__) . '/lib/Tests/Zikula/FileSystem/AllTests.php';

class AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zikula Core - All Tests');
        $suite->addTest(I18nAllTests::suite());
        $suite->addTest(I18nAllTests::suite());
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}