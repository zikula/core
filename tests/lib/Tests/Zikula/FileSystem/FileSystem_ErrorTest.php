<?php
require_once dirname(__FILE__) . '/../../../../bootstrap.php';

/**
 * Zikula_FileSystem_Error test case.
 */
class Zikula_FileSystem_ErrorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Zikula_FileSystem_Error
     */
    private $Zikula_FileSystem_Error;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $config = new Zikula_FileSystem_Configuration_Ftp();
        $this->Zikula_FileSystem_Ftp = new Zikula_FileSystem_Ftp($config);
        $this->Zikula_FileSystem_Ftp->getErrorHandler()->register('Error', 1);
        $this->Zikula_FileSystem_Ftp->getErrorHandler()->register('Error2', 2);

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->Zikula_FileSystem_Error = null;
        parent::tearDown();
    }

    /**
     * Tests Zikula_FileSystem_Error->error_get_last()
     */
    public function testErrorGetLast()
    {
        $this->assertType('array', $this->Zikula_FileSystem_Ftp->getErrorHandler()->getLast());
        $config = new Zikula_FileSystem_Configuration_Ftp();
        $fs = new Zikula_FileSystem_Ftp($config);
        $this->assertEquals(false, $fs->getErrorHandler()->getLast());
        $fs = new Zikula_FileSystem_Ftp($config);
        $fs->getErrorHandler()->register('Error', 1);
        $fs->getErrorHandler()->register('Error2', 2);
        $this->assertType('array', $fs->getErrorHandler()->getLast(true));
        $this->assertType('array', $fs->getErrorHandler()->getLast(true));
        $this->assertEquals(false, $fs->getErrorHandler()->getLast(true));
    }

    /**
     * Tests Zikula_FileSystem_Error->error_count()
     */
    public function testErrorCount()
    {
         $this->assertEquals(2, $this->Zikula_FileSystem_Ftp->getErrorHandler()->count());
    }

    /**
     * Tests Zikula_FileSystem_Error->error_get_all()
     */
    public function testErrorGetAll()
    {
        $config = new Zikula_FileSystem_Configuration_Ftp();
        $fs = new Zikula_FileSystem_Ftp($config);
        $fs->getErrorHandler()->register('Error', 1);
        $fs->getErrorHandler()->register('Error2', 2);
        $this->assertType('array', $fs->getErrorHandler()->getAll(true));
        $this->assertEquals(array(), $fs->getErrorHandler()->getAll(true));
    }

    /**
     * Tests Zikula_FileSystem_Error->error_clear_all()
     */
    public function testError_clear_all()
    {
        $config = new Zikula_FileSystem_Configuration_Ftp();
        $fs = new Zikula_FileSystem_Ftp($config);
        $fs->getErrorHandler()->register('Error', 1);
        $fs->getErrorHandler()->register('Error2', 2);
        $fs->getErrorHandler()->clearAll();
        $this->assertEquals(false, $fs->getErrorHandler()->getLast(true));
    }

    /**
     * Tests Zikula_FileSystem_Error->error_handler()
     */
    public function testError_handler()
    {
        $config = new Zikula_FileSystem_Configuration_Ftp();
        $fs = new Zikula_FileSystem_Ftp($config);
        $fs->getErrorHandler()->handler(0, 'Error', '1', '2');
        $this->assertType('array', $fs->getErrorHandler()->getAll(false));
        $this->assertEquals(1, $fs->getErrorHandler()->count(true));
    }

}