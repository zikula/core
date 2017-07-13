<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @covers \Zikula\Bundle\CoreBundle\Bundle\MetaData::getName
     */
    public function testGetName()
    {
        $this->assertEquals('zikula/admin-module', $this->metaData->getName());
    }

    /**
     * @covers \Zikula\Bundle\CoreBundle\Bundle\MetaData::getShortName
     */
    public function testShortName()
    {
        $this->assertEmpty($this->metaData->getShortName());
    }

    /**
     * @covers \Zikula\Bundle\CoreBundle\Bundle\MetaData::getPsr0
     */
    public function testGetPsr0()
    {
        $this->assertEquals($this->json['autoload']['psr-0'], $this->metaData->getPsr0());
    }

    /**
     * @covers \Zikula\Bundle\CoreBundle\Bundle\MetaData::getBasePath
     */
    public function testGetBasePath()
    {
        $this->assertEmpty($this->metaData->getBasePath());
    }

    /**
     * @covers \Zikula\Bundle\CoreBundle\Bundle\MetaData::getRootPath
     */
    public function testGetRootPath()
    {
        $this->assertEmpty($this->metaData->getRootPath());
    }

    /**
     * @covers \Zikula\Bundle\CoreBundle\Bundle\MetaData::getClass
     */
    public function testGetClass()
    {
        $this->assertEquals('Zikula\\AdminModule\\ZikulaAdminModule', $this->metaData->getClass());
    }

    /**
     * @covers \Zikula\Bundle\CoreBundle\Bundle\MetaData::getNamespace
     */
    public function testGetNamespace()
    {
        $this->assertEquals('Zikula\\AdminModule\\', $this->metaData->getNamespace());
    }

    private function getJson()
    {
        $json = <<<'EOF'
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
        "psr-0": { "Zikula\\AdminModule\\": "" }
    },
    "require": {
        "php": ">5.4.1"
    },
    "extra": {
        "zikula": {
            "class": "Zikula\\AdminModule\\ZikulaAdminModule",
            "core-compatibility": ">=1.4.2",
            "base-path": "",
            "root-path": "",
            "short-name": ""
        }
    }
}
EOF
        ;

        return json_decode($json, true);
    }
}
