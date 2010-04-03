<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Error.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/AbstractDriver.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Ftp.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Facade/Ftp.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Configuration.php';
require_once dirname(__FILE__) . '/../../../src/lib/FileSystem/Configuration/Ftp.php';

/**
 * FileSystem_Error test case.
 */
class FileSystem_ErrorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileSystem_Error
     */
    private $FileSystem_Error;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $config = new FileSystem_Configuration_Ftp();
        $this->FileSystem_Ftp = new FileSystem_Ftp($config);
        $this->FileSystem_Ftp->getErrorHandler()->register('Error', 1);
        $this->FileSystem_Ftp->getErrorHandler()->register('Error2', 2);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->FileSystem_Error = null;
        parent::tearDown();
    }

    /**
     * Tests FileSystem_Error->error_get_last()
     */
    public function testErrorGetLast()
    {
        $this->assertType('array', $this->FileSystem_Ftp->getErrorHandler()->getLast());
        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $this->assertEquals(false, $fs->getErrorHandler()->getLast());
        $fs = new FileSystem_Ftp($config);
        $fs->getErrorHandler()->register('Error', 1);
        $fs->getErrorHandler()->register('Error2', 2);
        $this->assertType('array', $fs->getErrorHandler()->getLast(true));
        $this->assertType('array', $fs->getErrorHandler()->getLast(true));
        $this->assertEquals(false, $fs->getErrorHandler()->getLast(true));
    }

    /**
     * Tests FileSystem_Error->error_count()
     */
    public function testErrorCount()
    {
         $this->assertEquals(2, $this->FileSystem_Ftp->getErrorHandler()->count());
    }

    /**
     * Tests FileSystem_Error->error_get_all()
     */
    public function testErrorGetAll()
    {
        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $fs->getErrorHandler()->register('Error', 1);
        $fs->getErrorHandler()->register('Error2', 2);
        $this->assertType('array', $fs->getErrorHandler()->getAll(true));
        $this->assertEquals(array(), $fs->getErrorHandler()->getAll(true));
    }

    /**
     * Tests FileSystem_Error->error_clear_all()
     */
    public function testError_clear_all()
    {
        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $fs->getErrorHandler()->register('Error', 1);
        $fs->getErrorHandler()->register('Error2', 2);
        $fs->getErrorHandler()->clearAll();
        $this->assertEquals(false, $fs->getErrorHandler()->getLast(true));
    }

    /**
     * Tests FileSystem_Error->error_handler()
     */
    public function testError_handler()
    {
        $config = new FileSystem_Configuration_Ftp();
        $fs = new FileSystem_Ftp($config);
        $fs->getErrorHandler()->handler(0, 'Error', '1', '2');
        $this->assertType('array', $fs->getErrorHandler()->getAll(false));
        $this->assertEquals(1, $fs->getErrorHandler()->count(true));
    }

}