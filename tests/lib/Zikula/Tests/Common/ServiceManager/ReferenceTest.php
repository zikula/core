<?php
namespace Zikula\Tests\Common\ServiceManager;
use Zikula\Common\ServiceManager\Reference;

class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Reference
     */
    protected $reference;

    protected function setUp()
    {
        $this->reference = new Reference('apple');
    }

    protected function tearDown()
    {
        $this->reference = null;
        parent::tearDown();
    }

    public function test__construct()
    {
        $this->assertAttributeSame('apple', 'id', $this->reference);
    }

    public function testGetId()
    {
        $this->assertSame('apple', $this->reference->getId());
    }
}
