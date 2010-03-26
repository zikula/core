<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../../src/lib/FileSystem/Configuration.php';
require_once dirname(__FILE__) . '/../../../../src/lib/FileSystem/Configuration/Sftp.php';

/**
 * FileSystem_Configuration_Sftp test case.
 */
class FileSystem_Configuration_SftpTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Configuration_Sftp
     */
    private $FileSystem_Configuration_Sftp;
    private $FileSystem_Configuration_Sftp2;
    private $FileSystem_Configuration_Sftp3;
    private $FileSystem_Configuration_Sftp4;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated FileSystem_Configuration_SftpTest::setUp()


        $this->FileSystem_Configuration_Sftp = new FileSystem_Configuration_Sftp('host', 'user', 'pass', '/', 22);
        $this->FileSystem_Configuration_Sftp2 = new FileSystem_Configuration_Sftp();
        $this->FileSystem_Configuration_Sftp3 = new FileSystem_Configuration_Sftp('host', 'user', 'pass', 'dir', 'port');
        $this->FileSystem_Configuration_Sftp4 = new FileSystem_Configuration_Sftp('host', 'user', 'pass', '/test', 'port');

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated FileSystem_Configuration_SftpTest::tearDown()


        $this->FileSystem_Configuration_Sftp = null;

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
     * Tests FileSystem_Configuration_Sftp->__construct()
     */
    public function test__construct()
    {
        // TODO Auto-generated FileSystem_Configuration_SftpTest->test__construct()
        $this->markTestIncomplete("__construct test not implemented");

        $this->FileSystem_Configuration_Sftp->__construct(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Sftp->getUser()
     */
    public function testGetUser()
    {
        $this->assertEquals('user', $this->FileSystem_Configuration_Sftp->getUser());
        $this->assertEquals('Anonymous', $this->FileSystem_Configuration_Sftp2->getUser());

    }

    /**
     * Tests FileSystem_Configuration_Sftp->getPass()
     */
    public function testGetPass()
    {
        $this->assertEquals('pass', $this->FileSystem_Configuration_Sftp->getPass());
        $this->assertEquals('', $this->FileSystem_Configuration_Sftp2->getPass());

    }

    /**
     * Tests FileSystem_Configuration_Sftp->getHost()
     */
    public function testGetHost()
    {
                $this->assertEquals('host',$this->FileSystem_Configuration_Sftp->getHost());
                $this->assertEquals('localhost',$this->FileSystem_Configuration_Sftp2->getHost());

    }

    /**
     * Tests FileSystem_Configuration_Sftp->getPort()
     */
    public function testGetPort()
    {
        $this->assertEquals('22', $this->FileSystem_Configuration_Sftp->getPort());
        $this->assertEquals('22', $this->FileSystem_Configuration_Sftp2->getPort());
        $this->assertEquals('22', $this->FileSystem_Configuration_Sftp3->getPort());

    }

    /**
     * Tests FileSystem_Configuration_Sftp->getDir()
     */
    public function testGetDir()
    {
        $this->assertEquals('/',$this->FileSystem_Configuration_Sftp->getDir());
        $this->assertEquals('./',$this->FileSystem_Configuration_Sftp2->getDir());
        $this->assertEquals('./dir',$this->FileSystem_Configuration_Sftp3->getDir());
        $this->assertEquals('/test',$this->FileSystem_Configuration_Sftp4->getDir());

    }

}

