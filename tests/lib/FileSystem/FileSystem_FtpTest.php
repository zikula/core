<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

// exclude the following file from code coverage reports.
PHPUnit_Util_Filter::addFileToFilter(dirname(__FILE__). '/../../../src/lib/FileSystem/Facade/Ftp.php');

require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Interface.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Error.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Driver.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Ftp.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Facade/Ftp.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Configuration.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Configuration/Ftp.php';


/**
 * FileSystem_Ftp test case.
 */
class FileSystem_FtpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Ftp
     */
    private $FileSystem_Ftp;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $config = new FileSystem_Configuration_Ftp();
        $this->FileSystem_Ftp = new FileSystem_Ftp($config);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->FileSystem_Ftp = null;
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests FileSystem_Ftp->connect()
     */
    public function testConnect()
    {
        $config = new FileSystem_Configuration_Ftp(1,2,3,4,5,6,true);
        $fs = new FileSystem_Ftp($config);
        $stub = $this->getMock('FileSystem_Facade_Ftp');
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

        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $stub = $this->getMock('FileSystem_Facade_Ftp');
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

        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $stub = $this->getMock('FileSystem_Facade_Ftp');
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

        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $stub = $this->getMock('FileSystem_Facade_Ftp');
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

        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $stub = $this->getMock('FileSystem_Facade_Ftp');
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

        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $stub = $this->getMock('FileSystem_Facade_Ftp');
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

    /**
     * Tests FileSystem_Ftp->put()
     */
    public function testPut()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('put')
             ->will($this->returnValue(true));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Ftp->put(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('put')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->put(1,2));
    }

    /**
     * Tests FileSystem_Ftp->fput()
     */
    public function testFput()
    {
        /// Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('fput')
             ->will($this->returnValue(true));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Ftp->fput(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('fput')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->fput(1,2));
    }

    /**
     * Tests FileSystem_Ftp->get()
     */
    public function testGet()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('get')
             ->will($this->returnValue(true));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Ftp->get(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('get')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->get(1,2));

    }

    /**
     * Tests FileSystem_Ftp->fget()
     */
    public function testFget()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('fget')
             ->will($this->returnValue(true));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertType('resource', $this->FileSystem_Ftp->fget(1));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('fget')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->fget(1));
    }

    /**
     * Tests FileSystem_Ftp->chmod()
     */
    public function testChmod()
    {
        // Configure the stub.
        $perm = '777';
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('chmod')
             ->will($this->returnValue((int)octdec(str_pad($perm, 4, '0', STR_PAD_LEFT))));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals($perm, $this->FileSystem_Ftp->chmod($perm,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('chmod')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->chmod(1,2));
    }

    /**
     * Tests FileSystem_Ftp->ls()
     */
    public function testLs()
    {
    	$array = array('1','2');
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('nlist')
             ->will($this->returnValue($array));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertType('array', $this->FileSystem_Ftp->ls(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('nlist')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->ls(1,2));
    }

    /**
     * Tests FileSystem_Ftp->cd()
     */
    public function testCd()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(true));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Ftp->cd(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('chdir')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->cd(1,2));
    }

    /**
     * Tests FileSystem_Ftp->mv()
     */
    public function testMv()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('rename')
             ->will($this->returnValue(true));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Ftp->mv(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('rename')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->mv(1,2));

    }

    /**
     * Tests FileSystem_Ftp->cp()
     */
    public function testCp()
    {
        // Configure the stub.
        $handle = fopen('php://temp', 'r+');
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('fget')
             ->will($this->returnValue($handle));
        $stub->expects($this->any())
             ->method('fput')
             ->will($this->returnValue(true));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Ftp->cp(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('fget')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->cp(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('fget')
             ->will($this->returnValue($handle));
        $stub->expects($this->any())
             ->method('fput')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->cp(1,2));
    }

    /**
     * Tests FileSystem_Ftp->rm()
     */
    public function testRm()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('delete')
             ->will($this->returnValue(true));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Ftp->rm(1,2));

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('delete')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->rm(1,2));
    }

    /**
     * Tests FileSystem_Ftp->isAlive()
     */
    public function testIsAlive()
    {
        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('systype')
             ->will($this->returnValue(true));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(true, $this->FileSystem_Ftp->isAlive());

        // Configure the stub.
        $stub = $this->getMock('FileSystem_Facade_Ftp');
        $stub->expects($this->any())
             ->method('systype')
             ->will($this->returnValue(false));

        $this->FileSystem_Ftp->setDriver($stub);
        $this->assertEquals(false, $this->FileSystem_Ftp->isAlive());
    }
}