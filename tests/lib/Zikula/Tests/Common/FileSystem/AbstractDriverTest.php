<?php
namespace Zikula\Tests\Common\FileSystem;
use Zikula\Common\FileSystem\Configuration\FtpConfiguration;
use Zikula\Common\FileSystem\Configuration\LocalConfiguration;
use Zikula\Common\FileSystem\Ftp;

/**
 * Zikula_FileSystem_Error test case.
 */
class AbstractDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_constructException()
    {
        $config = new LocalConfiguration();
        $driverAbstract = new Ftp($config);
    }
}