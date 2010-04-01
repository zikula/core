<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

// exclude the following file from code coverage reports.
PHPUnit_Util_Filter::addFileToFilter(dirname(__FILE__). '/../../../src/lib/FileSystem/Facade/Local.php');

require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Interface.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Configuration.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Configuration/Local.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Error.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Facade/Local.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Driver.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Local.php';

/**
 * FileSystem_Local test case.
 */
class FileSystem_LocalTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Local
     */
    private $FileSystem_Local;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $config = new FileSystem_Configuration_Local();
        $this->FileSystem_Local = new FileSystem_Local($config);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->FileSystem_Local = null;
        parent::tearDown();
    }

    /**
     * Tests FileSystem_Local->connect()
     */
    public function testConnect()
    {
        // Configure the stub.
        $config = new FileSystem_Configuration_Local();
        $fs = new FileSystem_Local($config);
        $fs->setDriver($config);
        $this->assertEquals(true, $fs->connect());
        $config = new FileSystem_Configuration_Local('/dir');
        $fs = new FileSystem_Local($config);
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(true, $fs->connect());
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(false));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());
    }

    /**
     * Tests FileSystem_Local->put()
     */
    public function testPut()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(true));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Local->put(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(false));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Local->put(1,2));

    }

    /**
     * Tests FileSystem_Local->fput()
     */
    public function testFput()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('putContents')
             ->will($this->returnValue(333));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(333, $this->FileSystem_Local->fput(1,2,3,4));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('putContents')
             ->will($this->returnValue(false));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Local->fput(1,2,3,4));

    }

    /**
     * Tests FileSystem_Local->get()
     */
    public function testGet()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(true));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Local->get(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(false));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Local->get(1,2));
    }

    /**
     * Tests FileSystem_Local->fget()
     */
    public function testFget()
    {
        // Configure the stub.
        $handle = fopen('php://temp', 'r+');
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('fileOpen')
             ->will($this->returnValue($handle));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertType('resource', $this->FileSystem_Local->fget(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('fileOpen')
             ->will($this->returnValue(false));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Local->fget(1,2));
    }

    /**
     * Tests FileSystem_Local->chmod()
     */
    public function testChmod()
    {
    	$perm = '777';
    	$perm2 = (int)octdec(str_pad($perm, 4, '0', STR_PAD_LEFT));
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('chmod')
             ->will($this->returnValue($perm2));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals($perm, $this->FileSystem_Local->chmod(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('chmod')
             ->will($this->returnValue(false));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Local->chmod(1,2));

    }

    /**
     * Tests FileSystem_Local->ls()
     */
    public function testLs()
    {
        // Configure the stub.
        $array = array('1','2');
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('scandir')
             ->will($this->returnValue($array));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertType('array', $this->FileSystem_Local->ls(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('scandir')
             ->will($this->returnValue(false));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Local->ls(1,2));
    }

    /**
     * Tests FileSystem_Local->cd()
     */
    public function testCd()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Local->cd(1));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(false));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Local->cd(1));

    }

    /**
     * Tests FileSystem_Local->mv()
     */
    public function testMv()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('rename')
             ->will($this->returnValue(true));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Local->mv(1,2,3));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('rename')
             ->will($this->returnValue(false));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Local->mv(1,2,3));
    }

    /**
     * Tests FileSystem_Local->cp()
     */
    public function testCp()
    {
       // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(true));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Local->cp(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('copy')
             ->will($this->returnValue(false));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Local->cp(1,2));
    }

    /**
     * Tests FileSystem_Local->rm()
     */
    public function testRm()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('delete')
             ->will($this->returnValue(true));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Local->rm(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Local');
        $stub->expects($this->any())
             ->method('delete')
             ->will($this->returnValue(false));

        $this->FileSystem_Local->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Local->rm(1,2));

    }
}