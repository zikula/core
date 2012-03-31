<?php
namespace Zikula\Component\FileSystem\Tests\Configuration;

use Zikula\Component\FileSystem\Configuration\FtpConfiguration;
use Zikula\Component\FileSystem\Configuration\ConfigurationInterface;
/**
 * Zikula_FileSystem_Configuration_Ftp test case.
 */
class FtpConfigurationTest extends \PHPUnit_Framework_TestCase
{
    private $ftp;
    private $ftp2;
    private $ftp3;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->ftp = new FtpConfiguration('host', 'user', 'pass', 'dir', 21, 10, false, true);
        $this->ftp2 = new FtpConfiguration('', 'user', 'pass', '', '', '', true, false);
        $this->ftp3 = new FtpConfiguration('', 'user', 'pass', '', '', '', '', '');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->ftp = null;
        $this->ftp2 = null;
        $this->ftp3 = null;

        parent::tearDown();
    }

    public function test__construct()
    {
        $this->assertInstanceOf('Zikula\Component\FileSystem\Configuration\ConfigurationInterface', $this->ftp);
    }

    public function testGetUser()
    {
        $this->assertEquals('user', $this->ftp->getUser());
    }

    public function testGetPass()
    {
        $this->assertEquals('pass', $this->ftp->getPass());
    }

    public function testGetHost()
    {
        $this->assertEquals('host', $this->ftp->getHost());
        $this->assertEquals('localhost', $this->ftp2->getHost());
    }

    public function testGetPort()
    {
        $this->assertEquals('21', $this->ftp->getPort());
        $this->assertEquals('990', $this->ftp2->getPort());
        $this->assertEquals('21', $this->ftp3->getPort());
    }

    public function testGetDir()
    {
        $this->assertEquals('dir', $this->ftp->getDir());
        $this->assertEquals('/', $this->ftp2->getDir());
    }

    public function testGetTimeout()
    {
        $this->assertEquals('10', $this->ftp->getTimeout());
        $this->assertEquals('10', $this->ftp2->getTimeout());
    }

    public function testGetSSL()
    {
        $this->assertEquals(false, $this->ftp->getSsl());
        $this->assertEquals(true, $this->ftp2->getSsl());
        $this->assertEquals(false, $this->ftp3->getSsl());
    }

    public function testGetPasv()
    {
        $this->assertEquals(true, $this->ftp->getPasv());
        $this->assertEquals(false, $this->ftp2->getPasv());
        $this->assertEquals(true, $this->ftp3->getPasv());
    }

}
