<?php 
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Example_AllTests::main');
}

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/ExampleTest.php';

class Example_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Example Tests');
        $suite->addTestSuite('ExampleTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Example_AllTests::main') {
    Example_AllTests::main();
}