<?php
require_once dirname(__FILE__) . '/bootstrap.php';
 
class DummyTest extends PHPUnit_Framework_TestCase
{
    public function testDummy()
    {
        $this->assertTrue(true);
    }
}