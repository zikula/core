<?php
require_once dirname(__FILE__) . '/../../../../../bootstrap.php';

/**
 * Zikula_FileSystem_Configuration_Sftp test case.
 */
class Zikula_FileSystem_Configuration_SftpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Zikula_FileSystem_Configuration_Sftp
     */
    private $Zikula_FileSystem_Configuration_Sftp;
    private $Zikula_FileSystem_Configuration_Sftp2;
    private $Zikula_FileSystem_Configuration_Sftp3;
    private $Zikula_FileSystem_Configuration_Sftp4;
    private $Zikula_FileSystem_Configuration_Sftp5;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated Zikula_FileSystem_Configuration_SftpTest::setUp()


        $this->Zikula_FileSystem_Configuration_Sftp = new Zikula_FileSystem_Configuration_Sftp('host', 'user', 'pass', '/', 22);
        $this->Zikula_FileSystem_Configuration_Sftp2 = new Zikula_FileSystem_Configuration_Sftp();
        $this->Zikula_FileSystem_Configuration_Sftp3 = new Zikula_FileSystem_Configuration_Sftp('host', 'user', 'pass', 'dir', 'port');
        $this->Zikula_FileSystem_Configuration_Sftp4 = new Zikula_FileSystem_Configuration_Sftp('host', 'user', 'pass', '/test', 'port');
        $this->Zikula_FileSystem_Configuration_Sftp5 = new Zikula_FileSystem_Configuration_Sftp('host', 'user', '', '', 22, 'ssh-rsa', 'pubkey', 'privkey', 'passphrase');

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated Zikula_FileSystem_Configuration_SftpTest::tearDown()


        $this->Zikula_FileSystem_Configuration_Sftp = null;

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
     * Tests Zikula_FileSystem_Configuration_Sftp->__construct()
     */
    public function test__construct()
    {
	    $this->assertType('Zikula_FileSystem_Configuration', $this->Zikula_FileSystem_Configuration_Sftp);
	    $this->assertType('Zikula_FileSystem_Configuration_Sftp', $this->Zikula_FileSystem_Configuration_Sftp);
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Sftp->getUser()
     */
    public function testGetUser()
    {
        $this->assertEquals('user', $this->Zikula_FileSystem_Configuration_Sftp->getUser());
        $this->assertEquals('Anonymous', $this->Zikula_FileSystem_Configuration_Sftp2->getUser());

    }

    /**
     * Tests Zikula_FileSystem_Configuration_Sftp->getPass()
     */
    public function testGetPass()
    {
        $this->assertEquals('pass', $this->Zikula_FileSystem_Configuration_Sftp->getPass());
        $this->assertEquals('', $this->Zikula_FileSystem_Configuration_Sftp2->getPass());

    }

    /**
     * Tests Zikula_FileSystem_Configuration_Sftp->getHost()
     */
    public function testGetHost()
    {
                $this->assertEquals('host',$this->Zikula_FileSystem_Configuration_Sftp->getHost());
                $this->assertEquals('localhost',$this->Zikula_FileSystem_Configuration_Sftp2->getHost());

    }

    /**
     * Tests Zikula_FileSystem_Configuration_Sftp->getPort()
     */
    public function testGetPort()
    {
        $this->assertEquals('22', $this->Zikula_FileSystem_Configuration_Sftp->getPort());
        $this->assertEquals('22', $this->Zikula_FileSystem_Configuration_Sftp2->getPort());
        $this->assertEquals('22', $this->Zikula_FileSystem_Configuration_Sftp3->getPort());

    }

    /**
     * Tests Zikula_FileSystem_Configuration_Sftp->getDir()
     */
    public function testGetDir()
    {
        $this->assertEquals('/',$this->Zikula_FileSystem_Configuration_Sftp->getDir());
        $this->assertEquals('./',$this->Zikula_FileSystem_Configuration_Sftp2->getDir());
        $this->assertEquals('./dir',$this->Zikula_FileSystem_Configuration_Sftp3->getDir());
        $this->assertEquals('/test',$this->Zikula_FileSystem_Configuration_Sftp4->getDir());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Sftp->getDir()
     */
    public function testGetPubkey()
    {
        $this->assertEquals('',$this->Zikula_FileSystem_Configuration_Sftp->getPubKey());
        $this->assertEquals('pubkey',$this->Zikula_FileSystem_Configuration_Sftp5->getPubKey());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Sftp->getDir()
     */
    public function testGetPrivKey()
    {
        $this->assertEquals('',$this->Zikula_FileSystem_Configuration_Sftp->getPrivKey());
        $this->assertEquals('privkey',$this->Zikula_FileSystem_Configuration_Sftp5->getPrivKey());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Sftp->getDir()
     */
    public function testGetPassphrase()
    {
        $this->assertEquals('',$this->Zikula_FileSystem_Configuration_Sftp->getPassphrase());
        $this->assertEquals('passphrase',$this->Zikula_FileSystem_Configuration_Sftp5->getPassphrase());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Sftp->getDir()
     */
    public function testGetAuthType()
    {
        $this->assertEquals('pass',$this->Zikula_FileSystem_Configuration_Sftp->getAuthType());
        $this->assertEquals('ssh-rsa',$this->Zikula_FileSystem_Configuration_Sftp5->getAuthType());
    }

}