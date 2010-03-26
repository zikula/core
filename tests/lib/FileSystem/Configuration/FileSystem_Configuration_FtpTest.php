<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../../src/lib/FileSystem/Configuration.php';
require_once dirname(__FILE__) . '/../../../../src/lib/FileSystem/Configuration/Ftp.php';


/**
 * FileSystem_Configuration_Ftp test case.
 */
class FileSystem_Configuration_FtpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Configuration_Ftp
     */
    private $FileSystem_Configuration_Ftp;
    private $FileSystem_Configuration_Ftp2;
    private $FileSystem_Configuration_Ftp3;


    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated FileSystem_Configuration_FtpTest::setUp()


        $this->FileSystem_Configuration_Ftp = new FileSystem_Configuration_Ftp('host', 'user', 'pass', 'dir', 21, 10, false, true);
	$this->FileSystem_Configuration_Ftp2 = new FileSystem_Configuration_Ftp('', 'user', 'pass', '', '', '', true, false);
	$this->FileSystem_Configuration_Ftp3 = new FileSystem_Configuration_Ftp('', 'user', 'pass', '', '', '', '', '');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest::tearDown()


        $this->FileSystem_Configuration_Ftp = null;

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
     * Tests FileSystem_Configuration_Ftp->__construct()
     */
    public function test__construct()
    {
        // TODO Auto-generated FileSystem_Configuration_FtpTest->test__construct()
        $this->markTestIncomplete("__construct test not implemented");

        $this->FileSystem_Configuration_Ftp->__construct(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getUser()
     */
    public function testGetUser()
    {
        $this->assertEquals('user', $this->FileSystem_Configuration_Ftp->getUser());

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getPass()
     */
    public function testGetPass()
    {
        $this->assertEquals('pass', $this->FileSystem_Configuration_Ftp->getPass());

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getHost()
     */
    public function testGetHost()
    {
        $this->assertEquals('host', $this->FileSystem_Configuration_Ftp->getHost());
        $this->assertEquals('localhost', $this->FileSystem_Configuration_Ftp2->getHost());

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getPort()
     */
    public function testGetPort()
    {
        $this->assertEquals('21', $this->FileSystem_Configuration_Ftp->getPort());
        $this->assertEquals('990', $this->FileSystem_Configuration_Ftp2->getPort());
        $this->assertEquals('21', $this->FileSystem_Configuration_Ftp3->getPort());

    }

    /**
     * Tests FileSystem_Configuration_Ftp->getDir()
     */
    public function testGetDir()
    {
        $this->assertEquals('dir', $this->FileSystem_Configuration_Ftp->getDir());
        $this->assertEquals('/', $this->FileSystem_Configuration_Ftp2->getDir());
    }

    /**
     * Tests FileSystem_Configuration_Ftp->getTimeout()
     */
    public function testGetTimeout()
    {
        $this->assertEquals('10', $this->FileSystem_Configuration_Ftp->getTimeout());
        $this->assertEquals('10', $this->FileSystem_Configuration_Ftp2->getTimeout());
    }

    /**
     * Tests FileSystem_Configuration_Ftp->getSSL()
     */
    public function testGetSSL()
    {
        $this->assertEquals(false, $this->FileSystem_Configuration_Ftp->getSSL());
        $this->assertEquals(true, $this->FileSystem_Configuration_Ftp2->getSSL());
        $this->assertEquals(false, $this->FileSystem_Configuration_Ftp3->getSSL());
    }

    /**
     * Tests FileSystem_Configuration_Ftp->getPasv()
     */
    public function testGetPasv()
    {
        $this->assertEquals(true, $this->FileSystem_Configuration_Ftp->getPasv());
        $this->assertEquals(false, $this->FileSystem_Configuration_Ftp2->getPasv());
        $this->assertEquals(true, $this->FileSystem_Configuration_Ftp3->getPasv());
    }

}

