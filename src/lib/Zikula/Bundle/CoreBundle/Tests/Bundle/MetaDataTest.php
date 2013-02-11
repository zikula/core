<?php
namespace Zikula\Bundle\CoreBundle\Tests\Bundle;

use Zikula\Bundle\CoreBundle\Bundle\MetaData;

class MetaDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetaData
     */
    protected $metaData;

    protected $json;

    protected function setUp()
    {
        $this->json = $this->getJson();
        $this->metaData = new MetaData($this->json);
    }

    protected function tearDown()
    {
        $this->metaData = null;
    }

    /**
     * @covers Zikula\Bundle\CoreBundle\Bundle\MetaData::getName
     */
    public function testGetName()
    {
        $this->assertEquals('zikula/admin-module', $this->metaData->getName());
    }

    /**
     * @covers Zikula\Bundle\CoreBundle\Bundle\MetaData::getPsr0
     */
    public function testGetPsr0()
    {
        $this->assertEquals($this->json->autoload->{'psr-0'}, $this->metaData->getPsr0());
    }

    /**
     * @covers Zikula\Bundle\CoreBundle\Bundle\MetaData::getBasePath
     */
    public function testGetBasePath()
    {
        $this->assertEquals('', $this->metaData->getBasePath());
    }

    /**
     * @covers Zikula\Bundle\CoreBundle\Bundle\MetaData::getClass
     */
    public function testGetClass()
    {
        $this->assertEquals('Admin\\AdminModule', $this->metaData->getClass());
    }

    /**
     * @covers Zikula\Bundle\CoreBundle\Bundle\MetaData::getNamespace
     */
    public function testGetNamespace()
    {
        $this->assertEquals('Admin\\', $this->metaData->getNamespace());
    }

    private function getJson()
    {
        return json_decode(<<<'EOF'
{
    "name": "zikula/admin-module",
    "description": "Backend Administration Module",
    "type": "zikula-module",
    "license": "LGPL-3.0+",
    "authors": [
        {
            "name": "Zikula",
            "homepage": "http://zikula.org/"
        }
    ],
    "autoload": {
        "psr-0": { "Admin\\": "" }
    },
    "require": {
        "php": ">5.3.3"
    },
    "extra": {
        "zikula": {
            "class": "Admin\\AdminModule",
            "base-path": ""
        }
    }
}
EOF
);
    }
}
