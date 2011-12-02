<?php
namespace Zikula\Tests\Common;

class Foo
{
    protected $data = 'unset';
    protected $eventManager= 'unset';
    protected $ServiceManager= 'unset';
    protected $_private= 'unset';
    public $public= 'unset';

    public function __construct($provider)
    {
        ClassProperties::load($this, $provider);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function setServiceManager($ServiceManager)
    {
        $this->ServiceManager = $ServiceManager;
    }

    public function set_private($_private)
    {
        $this->_private = $_private;
    }

    public function setPublic($public)
    {
        $this->public = $public;
    }
}

/**
 * ClassProperties test case.
 */
class ClassPropertiesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests ClassProperties::setters()
     * @dataProvider loadProvider
     */
    public function testLoad($test, $input, $expected)
    {
        $foo = new Foo($input);
        if ($expected) {
            foreach ($expected as $k => $v) {
                $this->$test($v, $k, $foo);
            }
        }
    }

    public function loadProvider()
    {
        return array(
            array('assertAttributeSame', array('data' => '1', 'eventmanager' => '2', 'servicemanager' => '3', '_private' => '4', 'public' => '5'),
                                         array('data' => '1', 'eventManager' => '2', 'ServiceManager' => '3', '_private' => '4', 'public' => '5')),

            array('assertAttributeSame', array('daFta' => '1', 'eveSntmanager' => '2', 'servicemaDnager' => '3', '_privateX' => '4', 'pubXlic' => '5'),
                                         array('data' => 'unset', 'eventManager' => 'unset', 'ServiceManager' => 'unset', '_private' => 'unset', 'public' => 'unset')),

            array('assertAttributeSame', array('Data' => null, 'Eventmanager' => '2', 'SERVICEMANAGER' => $this, '_privatXe' => '4', 'public' => '5'),
                                         array('data' => null, 'eventManager' => '2', 'ServiceManager' => $this, '_private' => 'unset', 'public' => '5')),

            array('assertAttributeSame', array('1' => '1', '2' => '2', '3' => '3', '_private' => '4', 'public' => '5'),
                                         array('data' => 'unset', 'eventManager' => 'unset', 'ServiceManager' => 'unset', '_private' => '4', 'public' => '5')),

            array('assertAttributeNotSame', array('data' => '1', 'eventmanager' => '2', 'servicemanager' => '3', '_private' => '4', 'public' => '5'),
                                            array('data' => null, 'eventManager' => true, 'ServiceManager' => false, '_private' => 'Q', 'public' => 'Z')),

            array('assertAttributeSame', array('data' => 1, 'eventmanager' => '2', 'servicemanager' => 212.3, '_private' => '4', 'public' => array('hello')),
                                         array('data' => 1, 'eventManager' => '2', 'ServiceManager' => 212.3, '_private' => '4', 'public' => array('hello'))),

            // test when no attributes are sent in
            array('assertAttributeSame', array(), array()),
            );
    }

}

