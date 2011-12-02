<?php
namespace Zikula\Tests\Common\FileSystem\Configuration;

use Zikula\Common\FileSystem\Sftp;
use Zikula\Common\FileSystem\Configuration\SftpConfiguration;

/**
 * Zikula_FileSystem_Sftp test case.
 */
class SftpTest extends PHPUnit_Framework_TestCase
{
    private $sftp;

    protected function setUp()
    {
        parent::setUp();

        $config = new SftpConfiguration();
        $this->sftp = new Sftp($config);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->sftp = null;
        parent::tearDown();
    }

    /**
     * Tests Zikula_FileSystem_Sftp->connect()
     */
    public function testConnect()
    {
        $config = new SftpConfiguration(1,2,3,4,5);
        $fs = new Sftp($config);
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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

        $config = new SftpConfiguration(1,2,3,4,5, 'ssh-rsa', 'pubkey', 'privkey', 'passphrase');
        $fs = new Sftp($config);
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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

        $config = new SftpConfiguration(1,2,3,4,5, 'ssh-rsa', 'pubkey', 'privkey', 'passphrase');
        $fs = new Sftp($config);
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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

    public function testPut()
    {
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('scpSend')
             ->will($this->returnValue(true));
        $this->sftp->setDriver($stub);
        $this->assertEquals(true,$this->sftp->put(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('scpSend')
             ->will($this->returnValue(false));
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->put(1,2));

    }

    public function testFput()
    {
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('putContents')
             ->will($this->returnValue(true));
        $this->sftp->setDriver($stub);
        $this->assertEquals(true,$this->sftp->fput(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('putContents')
             ->will($this->returnValue(false));
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->fput(1,2));

    }

    public function testGet()
    {
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('scpRecv')
             ->will($this->returnValue(true));
        $this->sftp->setDriver($stub);
        $this->assertEquals(true,$this->sftp->get(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('scpRecv')
             ->will($this->returnValue(false));
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->get(1,2));

    }

    public function testFget()
    {
    	$handle = fopen('php://temp', 'r+');
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('sftpFopen')
             ->will($this->returnValue($handle));
        $this->sftp->setDriver($stub);
        $this->assertInternalType('resource',$this->sftp->fget(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('sftpFopen')
             ->will($this->returnValue(false));
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->fget(1,2));

    }

    public function testChmod()
    {
    	$perm = 777;
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals($perm,$this->sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->chmod($perm,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->chmod($perm,2));

        $perm = 'b747';
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->chmod($perm,2));
    }

    public function testLs()
    {
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertInternalType('array',$this->sftp->ls());

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->ls());

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->ls());
    }

    public function testCd()
    {
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $this->sftp->setDriver($stub);
        $this->assertEquals(true,$this->sftp->cd(1));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->cd(1));
    }

    public function testMv()
    {
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpRename')
             ->will($this->returnValue(true));
        $this->sftp->setDriver($stub);
        $this->assertEquals(true,$this->sftp->mv(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpRename')
             ->will($this->returnValue(true));
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->mv(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpRename')
             ->will($this->returnValue(false));
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->mv(1,2));

    }

    public function testCp()
    {
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(true,$this->sftp->cp(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->cp(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->cp(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->cp(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->cp(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->cp(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->cp(1,2));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
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
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->cp(1,2));

    }

    public function testRm()
    {
        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpDelete')
             ->will($this->returnValue(true));
        $this->sftp->setDriver($stub);
        $this->assertEquals(true,$this->sftp->rm(1));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(false));
        $stub->expects($this->any())
             ->method('sftpDelete')
             ->will($this->returnValue(true));
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->rm(1));

        $stub = $this->getMock('Zikula\Common\FileSystem\Facade\SftpFacade');
        $stub->expects($this->any())
             ->method('realpath')
             ->will($this->returnValue(true));
        $stub->expects($this->any())
             ->method('sftpDelete')
             ->will($this->returnValue(false));
        $this->sftp->setDriver($stub);
        $this->assertEquals(false,$this->sftp->rm(1));
    }
}