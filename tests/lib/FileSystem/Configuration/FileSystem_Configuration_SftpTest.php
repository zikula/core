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

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated FileSystem_Configuration_SftpTest::setUp()


        $this->FileSystem_Configuration_Sftp = new FileSystem_Configuration_Sftp(/* parameters */);

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
        // TODO Auto-generated FileSystem_Configuration_SftpTest->testGetUser()
        $this->markTestIncomplete("getUser test not implemented");

        $this->FileSystem_Configuration_Sftp->getUser(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Sftp->getPass()
     */
    public function testGetPass()
    {
        // TODO Auto-generated FileSystem_Configuration_SftpTest->testGetPass()
        $this->markTestIncomplete("getPass test not implemented");

        $this->FileSystem_Configuration_Sftp->getPass(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Sftp->getHost()
     */
    public function testGetHost()
    {
        // TODO Auto-generated FileSystem_Configuration_SftpTest->testGetHost()
        $this->markTestIncomplete("getHost test not implemented");

        $this->FileSystem_Configuration_Sftp->getHost(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Sftp->getPort()
     */
    public function testGetPort()
    {
        // TODO Auto-generated FileSystem_Configuration_SftpTest->testGetPort()
        $this->markTestIncomplete("getPort test not implemented");

        $this->FileSystem_Configuration_Sftp->getPort(/* parameters */);

    }

    /**
     * Tests FileSystem_Configuration_Sftp->getDir()
     */
    public function testGetDir()
    {
        // TODO Auto-generated FileSystem_Configuration_SftpTest->testGetDir()
        $this->markTestIncomplete("getDir test not implemented");

        $this->FileSystem_Configuration_Sftp->getDir(/* parameters */);

    }

}

