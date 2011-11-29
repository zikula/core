<?php
require_once dirname(__FILE__) . '/../../../../bootstrap.php';

/**
 * Zikula_FileSystem_Sftp test case.
 */
class Zikula_FileSystem_SftpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Zikula_FileSystem_Sftp
     */
    private $Zikula_FileSystem_Sftp;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        $config = new Zikula_FileSystem_Configuration_Sftp();
        $this->Zikula_FileSystem_Sftp = new Zikula_FileSystem_Sftp($config);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->Zikula_FileSystem_Sftp = null;
        parent::tearDown();
    }

    /**
     * Tests Zikula_FileSystem_Sftp->connect()
     */
    public function testConnect()
    {
        $config = new Zikula_FileSystem_Configuration_Sftp(1,2,3,4,5);
        $fs = new Zikula_FileSystem_Sftp($config);
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('authPassword')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpStart')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(true, $fs->connect());

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('authPassword')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpStart')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('authPassword')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpStart')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('authPassword')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpStart')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('authPassword')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpStart')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());

        $config = new Zikula_FileSystem_Configuration_Sftp(1,2,3,4,5, 'ssh-rsa', 'pubkey', 'privkey', 'passphrase');
        $fs = new Zikula_FileSystem_Sftp($config);
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('authPubkey')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpStart')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(true, $fs->connect());

        $config = new Zikula_FileSystem_Configuration_Sftp(1,2,3,4,5, 'ssh-rsa', 'pubkey', 'privkey', 'passphrase');
        $fs = new Zikula_FileSystem_Sftp($config);
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('connect')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('authPubkey')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpStart')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $fs->setDriver($stub);
        $this->assertEquals(false, $fs->connect());
    }

    /**
     * Tests Zikula_FileSystem_Sftp->put()
     */
    public function testPut()
    {
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('scpSend')
             ->will($this->returnValue(true));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->Zikula_FileSystem_Sftp->put(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('scpSend')
             ->will($this->returnValue(false));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->put(1,2));

    }

    /**
     * Tests Zikula_FileSystem_Sftp->fput()
     */
    public function testFput()
    {
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('putContents')
             ->will($this->returnValue(true));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->Zikula_FileSystem_Sftp->fput(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('putContents')
             ->will($this->returnValue(false));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->fput(1,2));

    }

    /**
     * Tests Zikula_FileSystem_Sftp->get()
     */
    public function testGet()
    {
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('scpRecv')
             ->will($this->returnValue(true));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->Zikula_FileSystem_Sftp->get(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('scpRecv')
             ->will($this->returnValue(false));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->get(1,2));

    }

    /**
     * Tests Zikula_FileSystem_Sftp->fget()
     */
    public function testFget()
    {
    	$handle = fopen('php://temp', 'r+');
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('sftpFopen')
             ->will($this->returnValue($handle));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertInternalType('resource',$this->Zikula_FileSystem_Sftp->fget(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('sftpFopen')
             ->will($this->returnValue(false));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->fget(1,2));

    }

    /**
     * Tests Zikula_FileSystem_Sftp->chmod()
     */
    public function testChmod()
    {
    	$perm = 777;
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals($perm,$this->Zikula_FileSystem_Sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->chmod($perm,2));

        $perm = 'b747';
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->chmod($perm,2));
    }

    /**
     * Tests Zikula_FileSystem_Sftp->ls()
     */
    public function testLs()
    {
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('sftpIsDir')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpOpenDir')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpFileExists')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpReadDir')
             ->will($this->onConsecutiveCalls(true,false,false,false));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertInternalType('array',$this->Zikula_FileSystem_Sftp->ls());

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('sftpIsDir')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpFileExists')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpOpenDir')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpReadDir')
             ->will($this->returnValue(false));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->ls());

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->ls());
    }

    /**
     * Tests Zikula_FileSystem_Sftp->cd()
     */
    public function testCd()
    {
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->Zikula_FileSystem_Sftp->cd(1));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->cd(1));
    }

    /**
     * Tests Zikula_FileSystem_Sftp->mv()
     */
    public function testMv()
    {
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpRename')
             ->will($this->returnValue(true));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->Zikula_FileSystem_Sftp->mv(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpRename')
             ->will($this->returnValue(true));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->mv(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpRename')
             ->will($this->returnValue(false));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->mv(1,2));

    }

    /**
     * Tests Zikula_FileSystem_Sftp->cp()
     */
    public function testCp()
    {
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->Zikula_FileSystem_Sftp->cp(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->cp(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->cp(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->cp(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->cp(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->cp(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->cp(1,2));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
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
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->cp(1,2));

    }

    /**
     * Tests Zikula_FileSystem_Sftp->rm()
     */
    public function testRm()
    {
        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpDelete')
             ->will($this->returnValue(true));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(true,$this->Zikula_FileSystem_Sftp->rm(1));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpDelete')
             ->will($this->returnValue(true));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->rm(1));

        $stub = $this->getMock('Zikula_FileSystem_Facade_Sftp');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpDelete')
             ->will($this->returnValue(false));
        $this->Zikula_FileSystem_Sftp->setDriver($stub);
        $this->assertEquals(false,$this->Zikula_FileSystem_Sftp->rm(1));
    }
}