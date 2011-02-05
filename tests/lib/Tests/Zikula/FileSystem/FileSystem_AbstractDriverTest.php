<?php
require_once dirname(__FILE__) . '/../../../../bootstrap.php';

/**
 * Zikula_FileSystem_Error test case.
 */
class Zikula_FileSystem_AbstractDriverTest extends PHPUnit_Framework_TestCase
{
    public function test_construct()
    {
        try {
            $config = new Zikula_FileSystem_Configuration_Ftp();
            $driverAbstract = new Zikula_FileSystem_Ftp($config);
        } catch (InvalidArgumentException $expected) {
            $this->fail('Should not be an exception here');
        }
        $this->setExpectedException('InvalidArgumentException');
        $config = new Zikula_FileSystem_Configuration_Sftp();
        $driverAbstract = new Zikula_FileSystem_Ftp($config);
    }
}