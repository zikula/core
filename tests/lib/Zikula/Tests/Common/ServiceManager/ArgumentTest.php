<?php
namespace Zikula\Tests\Common\ServiceManager;
use Zikula\Common\ServiceManager\Argument;

class ArgumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Argument
     */
    protected $argument;

    protected function setUp()
    {
        $this->argument = new Argument('38423');
    }

    public function testGetId()
    {
        $this->assertEquals('38423', $this->argument->getId());
    }

    public function testConstruct()
    {
        $this->assertAttributeEquals('38423', 'id', $this->argument);
    }

}
