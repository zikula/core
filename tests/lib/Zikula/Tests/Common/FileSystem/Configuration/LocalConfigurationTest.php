<?php
namespace Zikula\Tests\Common\FileSystem\Configuration;

use Zikula\Common\FileSystem\Configuration\LocalConfiguration;
use Zikula\Common\FileSystem\Configuration\ConfigurationInterface;

/**
 * Zikula_FileSystem_Configuration_Local test case.
 */
class LocalConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Zikula_FileSystem_Configuration_Local
     */
    private $local;
    private $local2;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->local = new LocalConfiguration('dir');
        $this->local2 = new LocalConfiguration();

    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->local = null;
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
    }

    /**
     * Tests Local->__construct()
     */
    public function test__construct()
    {
        $this->assertInstanceOf('Zikula\Common\FileSystem\Configuration\ConfigurationInterface', $this->local);
        $this->assertAttributeEquals('dir', 'dir', $this->local);
	    $this->assertAttributeEquals('', 'dir', $this->local2);
    }

    /**
     * Tests LocalConfiguration->getDir()
     */
    public function testGetDir()
    {
        $this->assertEquals('dir',$this->local->getDir());
        $this->assertEquals('',$this->local2->getDir());
    }

}

