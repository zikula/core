<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Interface.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Error.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Driver.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Sftp.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Facade/Sftp.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Configuration.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Configuration/Sftp.php';

/**
 * FileSystem_Sftp test case.
 */
class FileSystem_SftpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Sftp
     */
    private $FileSystem_Sftp;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $config = new FileSystem_Configuration_Sftp();
        $this->FileSystem_Sftp = new FileSystem_Sftp($config);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated FileSystem_SftpTest::tearDown()


        $this->FileSystem_Sftp = null;

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
     * Tests FileSystem_Sftp->connect()
     */
    public function testConnect()
    {
        $config = new FileSystem_Configuration_Sftp(1,2,3,4,5);
        $fs = new FileSystem_Sftp($config);
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('authPassword')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftp')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(true, $fs->connect());
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('authPassword')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftp')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('authPassword')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftp')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('authPassword')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftp')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('authPassword')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftp')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());
    }

    /**
     * Tests FileSystem_Sftp->put()
     */
    public function testPut()
    {
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('scpSend')
             ->will($this->returnValue(true));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->FileSystem_Sftp->put(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('scpSend')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->put(1,2));

    }

    /**
     * Tests FileSystem_Sftp->fput()
     */
    public function testFput()
    {
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('putContents')
             ->will($this->returnValue(true));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->FileSystem_Sftp->fput(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('putContents')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->fput(1,2));

    }

    /**
     * Tests FileSystem_Sftp->get()
     */
    public function testGet()
    {
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('scpRecv')
             ->will($this->returnValue(true));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->FileSystem_Sftp->get(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('scpRecv')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->get(1,2));

    }

    /**
     * Tests FileSystem_Sftp->fget()
     */
    public function testFget()
    {
    	$handle = fopen('php://temp', 'r+');
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('sftpFopen')
             ->will($this->returnValue($handle));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertType('resource',$this->FileSystem_Sftp->fget(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('sftpFopen')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->fget(1,2));

    }

    /**
     * Tests FileSystem_Sftp->chmod()
     */
    public function testChmod()
    {
    	$perm = 777;
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(':::0:::'));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals($perm,$this->FileSystem_Sftp->chmod($perm,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(':::0:::'));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->chmod($perm,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(':::0:::'));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->chmod($perm,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(':::0:::'));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->chmod($perm,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->chmod($perm,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(":::1:::"));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->chmod($perm,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(":::2:::"));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->chmod($perm,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(''));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->chmod($perm,2));
    	
    }

    /**
     * Tests FileSystem_Sftp->ls()
     */
    public function testLs()
    {
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('sftpFileExists')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpIsDir')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpOpenDir')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpReadDir')
             ->will($this->onConsecutiveCalls(true,false,false,false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertType('array',$this->FileSystem_Sftp->ls());
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('sftpFileExists')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpIsDir')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpOpenDir')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpReadDir')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->ls());
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('sftpFileExists')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpIsDir')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpOpenDir')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpReadDir')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->ls());

    }

    /**
     * Tests FileSystem_Sftp->cd()
     */
    public function testCd()
    {
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->FileSystem_Sftp->cd(1));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->cd(1));
    }

    /**
     * Tests FileSystem_Sftp->mv()
     */
    public function testMv()
    {
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpRename')
             ->will($this->returnValue(true));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->FileSystem_Sftp->mv(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpRename')
             ->will($this->returnValue(true));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->mv(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpRename')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->mv(1,2));

    }

    /**
     * Tests FileSystem_Sftp->cp()
     */
    public function testCp()
    {
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(':::0:::'));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->FileSystem_Sftp->cp(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(':::0:::'));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->cp(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(':::0:::'));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->cp(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(':::0:::'));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->cp(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(":::1:::"));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->cp(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(":::2:::"));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->cp(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(''));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->cp(1,2));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShell')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellWrite')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sshShellRead')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->cp(1,2));

    }

    /**
     * Tests FileSystem_Sftp->rm()
     */
    public function testRm()
    {
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpDelete')
             ->will($this->returnValue(true));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->FileSystem_Sftp->rm(1));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpDelete')
             ->will($this->returnValue(true));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->rm(1));
        
        $stub = $this->getMock('FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpDelete')
             ->will($this->returnValue(false));
        $this->FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->FileSystem_Sftp->rm(1));
    }
    
/**
     * Tests FileSystem_Ftp->error_codes()
     */
    public function testError_codes()
    {
        $this->assertType('array',$this->FileSystem_Sftp->errorCodes());
    }
}