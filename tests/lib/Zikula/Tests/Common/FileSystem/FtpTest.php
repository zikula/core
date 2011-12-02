<?php
namespace Zikula\Tests\Common\FileSystem;

use Zikula\Common\FileSystem\Ftp;
use Zikula\Common\FileSystem\Configuration\FtpConfiguration;

/**
 * Zikula_FileSystem_Ftp test case.
 */
class FtpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zikula\Common\FileSystem\Ftp
     */
    private $ftp;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $config = new FtpConfiguration();
        $this->ftp = new Ftp($config);
    }

    protected function tearDown()
    {
        $this->ftp = null;
        parent::tearDown();
    }

    public function testConnect()
    {
        $config = new FtpConfiguration(1,2,3,4,5,6,true);
        $fs = new Ftp($config);
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('ssl_connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('login')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('pasv')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(true, $fs->connect());

        $config = new FtpConfiguration();
        $fs = new Ftp($config);
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('login')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('pasv')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(true, $fs->connect());

        $config = new FtpConfiguration();
        $fs = new Ftp($config);
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('login')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('pasv')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());

        $config = new FtpConfiguration();
        $fs = new Ftp($config);
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('login')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('pasv')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());

        $config = new FtpConfiguration();
        $fs = new Ftp($config);
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('login')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('pasv')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());

        $config = new FtpConfiguration();
        $fs = new Ftp($config);
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('login')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('pasv')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(false));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());
    }

    public function testPut()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('put')
             ->will($this->returnValue(true));

        $this->ftp->setDriver($stub);
        $this->assertEquals(true, $this->ftp->put(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('put')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->put(1,2));
    }

    /**
     * Tests Zikula_FileSystem_Ftp->fput()
     */
    public function testFput()
    {
        /// Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('fput')
             ->will($this->returnValue(true));

        $this->ftp->setDriver($stub);
        $this->assertEquals(true, $this->ftp->fput(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('fput')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->fput(1,2));
    }

    public function testGet()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('get')
             ->will($this->returnValue(true));

        $this->ftp->setDriver($stub);
        $this->assertEquals(true, $this->ftp->get(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('get')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->get(1,2));

    }

    public function testFget()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('fget')
             ->will($this->returnValue(true));

        $this->ftp->setDriver($stub);
        $this->assertInternalType('resource', $this->ftp->fget(1));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('fget')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->fget(1));
    }

    public function testChmod()
    {
        // Configure the stub.
        $perm = '777';
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('chmod')
             ->will($this->returnValue((int)octdec(str_pad($perm, 4, '0', STR_PAD_LEFT))));

        $this->ftp->setDriver($stub);
        $this->assertEquals($perm, $this->ftp->chmod($perm,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('chmod')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->chmod(1,2));
    }

    public function testLs()
    {
    	$array = array('1','2');
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('nlist')
             ->will($this->returnValue($array));

        $this->ftp->setDriver($stub);
        $this->assertInternalType('array', $this->ftp->ls(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('nlist')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->ls(1,2));
    }

    public function testCd()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));

        $this->ftp->setDriver($stub);
        $this->assertEquals(true, $this->ftp->cd(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->cd(1,2));
    }

    public function testMv()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('rename')
             ->will($this->returnValue(true));

        $this->ftp->setDriver($stub);
        $this->assertEquals(true, $this->ftp->mv(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('rename')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->mv(1,2));

    }

    public function testCp()
    {
        // Configure the stub.
        $handle = fopen('php://temp', 'r+');
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('fget')
             ->will($this->returnValue($handle));
        $stub->expects($this->any())
             ->method('fput')
             ->will($this->returnValue(true));

        $this->ftp->setDriver($stub);
        $this->assertEquals(true, $this->ftp->cp(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('fget')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->cp(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('fget')
             ->will($this->returnValue($handle));
        $stub->expects($this->any())
             ->method('fput')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->cp(1,2));
    }

    public function testRm()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('delete')
             ->will($this->returnValue(true));

        $this->ftp->setDriver($stub);
        $this->assertEquals(true, $this->ftp->rm(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('delete')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->rm(1,2));
    }

    public function testIsAlive()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('systype')
             ->will($this->returnValue(true));

        $this->ftp->setDriver($stub);
        $this->assertEquals(true, $this->ftp->isAlive());

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\FtpFacade');
        $stub->expects($this->any())
             ->method('systype')
             ->will($this->returnValue(false));

        $this->ftp->setDriver($stub);
        $this->assertEquals(false, $this->ftp->isAlive());
    }
}