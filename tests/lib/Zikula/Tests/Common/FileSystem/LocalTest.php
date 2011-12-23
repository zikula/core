<?php
namespace Zikula\Tests\Common\FileSystem;

use Zikula\Common\FileSystem\Local;
use Zikula\Common\FileSystem\Configuration\LocalConfiguration;

/**
 * Local test case.
 */
class LocalTest extends \PHPUnit_Framework_TestCase
{

    private $local;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $config = new LocalConfiguration();
        $this->local = new Local($config);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->local = null;
        parent::tearDown();
    }

    /**
     * Tests Local->connect()
     */
    public function testConnect()
    {
        // Configure the stub.
        $config = new LocalConfiguration();
        $fs = new Local($config);
        $fs->setDriver($config);
        $this->assertEquals(true, $fs->connect());
        $config = new LocalConfiguration('/dir');
        $fs = new Local($config);
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(true, $fs->connect());
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(false));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());
    }

    /**
     * Tests Local->put()
     */
    public function testPut()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(true));

        $this->local->setDriver($stub);
        $this->assertEquals(true, $this->local->put(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(false));

        $this->local->setDriver($stub);
        $this->assertEquals(false, $this->local->put(1,2));

    }

    /**
     * Tests Local->fput()
     */
    public function testFput()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('putContents')
             ->will($this->returnValue(333));

        $this->local->setDriver($stub);
        $this->assertEquals(333, $this->local->fput(1,2,3,4));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('putContents')
             ->will($this->returnValue(false));

        $this->local->setDriver($stub);
        $this->assertEquals(false, $this->local->fput(1,2,3,4));

    }

    /**
     * Tests Local->get()
     */
    public function testGet()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(true));

        $this->local->setDriver($stub);
        $this->assertEquals(true, $this->local->get(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(false));

        $this->local->setDriver($stub);
        $this->assertEquals(false, $this->local->get(1,2));
    }

    /**
     * Tests Local->fget()
     */
    public function testFget()
    {
        // Configure the stub.
        $handle = fopen('php://temp', 'r+');
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('fileOpen')
             ->will($this->returnValue($handle));

        $this->local->setDriver($stub);
        $this->assertInternalType('resource', $this->local->fget(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('fileOpen')
             ->will($this->returnValue(false));

        $this->local->setDriver($stub);
        $this->assertEquals(false, $this->local->fget(1,2));
    }

    /**
     * Tests Local->chmod()
     */
    public function testChmod()
    {
    	$perm = '777';
    	$perm2 = (int)octdec(str_pad($perm, 4, '0', STR_PAD_LEFT));
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('chmod')
             ->will($this->returnValue($perm2));

        $this->local->setDriver($stub);
        $this->assertEquals($perm, $this->local->chmod(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('chmod')
             ->will($this->returnValue(false));

        $this->local->setDriver($stub);
        $this->assertEquals(false, $this->local->chmod(1,2));

    }

    /**
     * Tests Local->ls()
     */
    public function testLs()
    {
        // Configure the stub.
        $array = array('1','2');
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('scandir')
             ->will($this->returnValue($array));

        $this->local->setDriver($stub);
        $this->assertInternalType('array', $this->local->ls(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('scandir')
             ->will($this->returnValue(false));

        $this->local->setDriver($stub);
        $this->assertEquals(false, $this->local->ls(1,2));
    }

    /**
     * Tests Local->cd()
     */
    public function testCd()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));

        $this->local->setDriver($stub);
        $this->assertEquals(true, $this->local->cd(1));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(false));

        $this->local->setDriver($stub);
        $this->assertEquals(false, $this->local->cd(1));

    }

    /**
     * Tests Local->mv()
     */
    public function testMv()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('rename')
             ->will($this->returnValue(true));

        $this->local->setDriver($stub);
        $this->assertEquals(true, $this->local->mv(1,2,3));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('rename')
             ->will($this->returnValue(false));

        $this->local->setDriver($stub);
        $this->assertEquals(false, $this->local->mv(1,2,3));
    }

    /**
     * Tests Local->cp()
     */
    public function testCp()
    {
       // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(true));

        $this->local->setDriver($stub);
        $this->assertEquals(true, $this->local->cp(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(false));

        $this->local->setDriver($stub);
        $this->assertEquals(false, $this->local->cp(1,2));
    }

    /**
     * Tests Local->rm()
     */
    public function testRm()
    {
        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('delete')
             ->will($this->returnValue(true));

        $this->local->setDriver($stub);
        $this->assertEquals(true, $this->local->rm(1,2));

        // Configure the stub.
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\LocalFacade');
        $stub->expects($this->any())
             ->method('delete')
             ->will($this->returnValue(false));

        $this->local->setDriver($stub);
        $this->assertEquals(false, $this->local->rm(1,2));

    }
}