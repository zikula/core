<?php
namespace Zikula\Tests\Common\FileSystem;
use Zikula\Common\FileSystem\Configuration\FtpConfiguration;
use Zikula\Common\FileSystem\Ftp;

/**
 * Zikula_FileSystem_Error test case.
 */
class AbstractDriverTest extends \PHPUnit_Framework_TestCase
{
    public function test_construct()
    {
        try {
            $config = new FtpConfiguration();
            $driverAbstract = new Ftp($config);
        } catch (\InvalidArgumentException $expected) {
            $this->fail('Should not be an exception here');
        }
        $this->setExpectedException('\InvalidArgumentException');
        $config = new FtpConfiguration();
        $driverAbstract = new Ftp($config);
    }
}