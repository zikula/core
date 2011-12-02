<?php
namespace Zikula\Tests\Common\FileSystem;

use Zikula\Common\FileSystem\Error;
use Zikula\Common\FileSystem\Ftp;
use Zikula\Common\FileSystem\Configuration\FtpConfiguration;

class ErrorTest extends \PHPUnit_Framework_TestCase
{

    private $error;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $config = new FtpConfiguration();
        $this->Ftp = new Ftp($config);
        $this->Ftp->getErrorHandler()->register('Error', 1);
        $this->Ftp->getErrorHandler()->register('Error2', 2);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->error = null;
        parent::tearDown();
    }

    public function testErrorGetLast()
    {
        $this->assertInternalType('array', $this->Ftp->getErrorHandler()->getLast());
        $config = new FtpConfiguration();
        $fs = new Ftp($config);
        $this->assertEquals(false, $fs->getErrorHandler()->getLast());
        $fs = new Ftp($config);
        $fs->getErrorHandler()->register('Error', 1);
        $fs->getErrorHandler()->register('Error2', 2);
        $this->assertInternalType('array', $fs->getErrorHandler()->getLast(true));
        $this->assertInternalType('array', $fs->getErrorHandler()->getLast(true));
        $this->assertEquals(false, $fs->getErrorHandler()->getLast(true));
    }

    public function testErrorCount()
    {
         $this->assertEquals(2, $this->Ftp->getErrorHandler()->count());
    }

    public function testErrorGetAll()
    {
        $config = new FtpConfiguration();
        $fs = new Ftp($config);
        $fs->getErrorHandler()->register('Error', 1);
        $fs->getErrorHandler()->register('Error2', 2);
        $this->assertInternalType('array', $fs->getErrorHandler()->getAll(true));
        $this->assertEquals(array(), $fs->getErrorHandler()->getAll(true));
    }

    public function testError_clear_all()
    {
        $config = new FtpConfiguration();
        $fs = new Ftp($config);
        $fs->getErrorHandler()->register('Error', 1);
        $fs->getErrorHandler()->register('Error2', 2);
        $fs->getErrorHandler()->clearAll();
        $this->assertEquals(false, $fs->getErrorHandler()->getLast(true));
    }

    public function testError_handler()
    {
        $config = new FtpConfiguration();
        $fs = new Ftp($config);
        $fs->getErrorHandler()->handler(0, 'Error', '1', '2');
        $this->assertInternalType('array', $fs->getErrorHandler()->getAll(false));
        $this->assertEquals(1, $fs->getErrorHandler()->count(true));
    }

}