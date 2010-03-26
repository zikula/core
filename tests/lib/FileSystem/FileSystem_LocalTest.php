<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Interface.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Error.php';
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

        // TODO Auto-generated FileSystem_LocalTest::setUp()


        $this->FileSystem_Local = new FileSystem_Local(/* parameters */);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated FileSystem_LocalTest::tearDown()


        $this->FileSystem_Local = null;

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
     * Tests FileSystem_Local->connect()
     */
    public function testConnect()
    {
        // TODO Auto-generated FileSystem_LocalTest->testConnect()
        $this->markTestIncomplete("connect test not implemented");

        $this->FileSystem_Local->connect(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->put()
     */
    public function testPut()
    {
        // TODO Auto-generated FileSystem_LocalTest->testPut()
        $this->markTestIncomplete("put test not implemented");

        $this->FileSystem_Local->put(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->fput()
     */
    public function testFput()
    {
        // TODO Auto-generated FileSystem_LocalTest->testFput()
        $this->markTestIncomplete("fput test not implemented");

        $this->FileSystem_Local->fput(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->get()
     */
    public function testGet()
    {
        // TODO Auto-generated FileSystem_LocalTest->testGet()
        $this->markTestIncomplete("get test not implemented");

        $this->FileSystem_Local->get(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->fget()
     */
    public function testFget()
    {
        // TODO Auto-generated FileSystem_LocalTest->testFget()
        $this->markTestIncomplete("fget test not implemented");

        $this->FileSystem_Local->fget(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->chmod()
     */
    public function testChmod()
    {
        // TODO Auto-generated FileSystem_LocalTest->testChmod()
        $this->markTestIncomplete("chmod test not implemented");

        $this->FileSystem_Local->chmod(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->ls()
     */
    public function testLs()
    {
        // TODO Auto-generated FileSystem_LocalTest->testLs()
        $this->markTestIncomplete("ls test not implemented");

        $this->FileSystem_Local->ls(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->cd()
     */
    public function testCd()
    {
        // TODO Auto-generated FileSystem_LocalTest->testCd()
        $this->markTestIncomplete("cd test not implemented");

        $this->FileSystem_Local->cd(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->mv()
     */
    public function testMv()
    {
        // TODO Auto-generated FileSystem_LocalTest->testMv()
        $this->markTestIncomplete("mv test not implemented");

        $this->FileSystem_Local->mv(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->cp()
     */
    public function testCp()
    {
        // TODO Auto-generated FileSystem_LocalTest->testCp()
        $this->markTestIncomplete("cp test not implemented");

        $this->FileSystem_Local->cp(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->rm()
     */
    public function testRm()
    {
        // TODO Auto-generated FileSystem_LocalTest->testRm()
        $this->markTestIncomplete("rm test not implemented");

        $this->FileSystem_Local->rm(/* parameters */);

    }

    /**
     * Tests FileSystem_Local->error_codes()
     */
    public function testError_codes()
    {
        // TODO Auto-generated FileSystem_LocalTest->testError_codes()
        $this->markTestIncomplete("error_codes test not implemented");

        $this->FileSystem_Local->error_codes(/* parameters */);

    }

}

