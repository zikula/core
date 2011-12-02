<?php
namespace Zikula\Tests\Common\FileSystem;

/**
 * Zikula_FileSystem_Error test case.
 */
class AbstractDriverTest extends PHPUnit_Framework_TestCase
{
    public function test_construct()
    {
        try {
            $config = new Configuration\Ftp();
            $driverAbstract = new Ftp($config);
        } catch (\InvalidArgumentException $expected) {
            $this->fail('Should not be an exception here');
        }
        $this->setExpectedException('\InvalidArgumentException');
        $config = new Configuration\Sftp();
        $driverAbstract = new Ftp($config);
    }
}