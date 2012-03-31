<?php
namespace Zikula\Component\FileSystem\Tests;

use Zikula\Component\FileSystem\Configuration\FtpConfiguration;
use Zikula\Component\FileSystem\Configuration\LocalConfiguration;
use Zikula\Component\FileSystem\Ftp;

/**
 * AbstractDriver test case.
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