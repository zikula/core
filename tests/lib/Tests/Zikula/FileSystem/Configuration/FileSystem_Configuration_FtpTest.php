<?php
require_once dirname(__FILE__) . '/../../../../../bootstrap.php';

/**
 * Zikula_FileSystem_Configuration_Ftp test case.
 */
class Zikula_FileSystem_Configuration_FtpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zikula_FileSystem_Configuration_Ftp
     */
    private $Zikula_FileSystem_Configuration_Ftp;
    private $Zikula_FileSystem_Configuration_Ftp2;
    private $Zikula_FileSystem_Configuration_Ftp3;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated Zikula_FileSystem_Configuration_FtpTest::setUp()


        $this->Zikula_FileSystem_Configuration_Ftp = new Zikula_FileSystem_Configuration_Ftp('host', 'user', 'pass', 'dir', 21, 10, false, true);
        $this->Zikula_FileSystem_Configuration_Ftp2 = new Zikula_FileSystem_Configuration_Ftp('', 'user', 'pass', '', '', '', true, false);
        $this->Zikula_FileSystem_Configuration_Ftp3 = new Zikula_FileSystem_Configuration_Ftp('', 'user', 'pass', '', '', '', '', '');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated Zikula_FileSystem_Configuration_FtpTest::tearDown()


        $this->Zikula_FileSystem_Configuration_Ftp = null;

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
     * Tests Zikula_FileSystem_Configuration_Ftp->__construct()
     */
    public function test__construct()
    {
        $this->assertInstanceOf('Zikula_FileSystem_ConfigurationInterface', $this->Zikula_FileSystem_Configuration_Ftp);
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Ftp->getUser()
     */
    public function testGetUser()
    {
        $this->assertEquals('user', $this->Zikula_FileSystem_Configuration_Ftp->getUser());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Ftp->getPass()
     */
    public function testGetPass()
    {
        $this->assertEquals('pass', $this->Zikula_FileSystem_Configuration_Ftp->getPass());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Ftp->getHost()
     */
    public function testGetHost()
    {
        $this->assertEquals('host', $this->Zikula_FileSystem_Configuration_Ftp->getHost());
        $this->assertEquals('localhost', $this->Zikula_FileSystem_Configuration_Ftp2->getHost());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Ftp->getPort()
     */
    public function testGetPort()
    {
        $this->assertEquals('21', $this->Zikula_FileSystem_Configuration_Ftp->getPort());
        $this->assertEquals('990', $this->Zikula_FileSystem_Configuration_Ftp2->getPort());
        $this->assertEquals('21', $this->Zikula_FileSystem_Configuration_Ftp3->getPort());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Ftp->getDir()
     */
    public function testGetDir()
    {
        $this->assertEquals('dir', $this->Zikula_FileSystem_Configuration_Ftp->getDir());
        $this->assertEquals('/', $this->Zikula_FileSystem_Configuration_Ftp2->getDir());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Ftp->getTimeout()
     */
    public function testGetTimeout()
    {
        $this->assertEquals('10', $this->Zikula_FileSystem_Configuration_Ftp->getTimeout());
        $this->assertEquals('10', $this->Zikula_FileSystem_Configuration_Ftp2->getTimeout());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Ftp->getSSL()
     */
    public function testGetSSL()
    {
        $this->assertEquals(false, $this->Zikula_FileSystem_Configuration_Ftp->getSSL());
        $this->assertEquals(true, $this->Zikula_FileSystem_Configuration_Ftp2->getSSL());
        $this->assertEquals(false, $this->Zikula_FileSystem_Configuration_Ftp3->getSSL());
    }

    /**
     * Tests Zikula_FileSystem_Configuration_Ftp->getPasv()
     */
    public function testGetPasv()
    {
        $this->assertEquals(true, $this->Zikula_FileSystem_Configuration_Ftp->getPasv());
        $this->assertEquals(false, $this->Zikula_FileSystem_Configuration_Ftp2->getPasv());
        $this->assertEquals(true, $this->Zikula_FileSystem_Configuration_Ftp3->getPasv());
    }

}
