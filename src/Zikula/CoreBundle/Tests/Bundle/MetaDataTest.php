<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Tests\Bundle;

use PHPUnit\Framework\TestCase;
use Zikula\AdminModule\ZikulaAdminModule;
use Zikula\Bundle\CoreBundle\Composer\MetaData;

class MetaDataTest extends TestCase
{
    /**
     * @var MetaData
     */
    protected $metaData;

    /**
     * @var array
     */
    protected $json;

    protected function setUp(): void
    {
        $this->json = $this->getJson();
        $this->metaData = new MetaData($this->json);
    }

    protected function tearDown(): void
    {
        $this->metaData = null;
    }

    /**
     * @covers MetaData::getName
     */
    public function testGetName(): void
    {
        $this->assertEquals('zikula/admin-module', $this->metaData->getName());
    }

    /**
     * @covers MetaData::getShortName
     */
    public function testShortName(): void
    {
        $this->assertEmpty($this->metaData->getShortName());
    }

    /**
     * @covers MetaData::getPsr0
     */
    public function testGetPsr0(): void
    {
        $this->assertEquals($this->json['autoload']['psr-0'], $this->metaData->getPsr0());
    }

    /**
     * @covers MetaData::getClass
     */
    public function testGetClass(): void
    {
        $this->assertEquals(ZikulaAdminModule::class, $this->metaData->getClass());
    }

    /**
     * @covers MetaData::getNamespace
     */
    public function testGetNamespace(): void
    {
        $this->assertEquals('Zikula\\AdminModule\\', $this->metaData->getNamespace());
    }

    private function getJson(): array
    {
        $json = <<<'EOF'
{
    "name": "zikula/admin-module",
    "description": "Backend Administration Module",
    "type": "zikula-module",
    "license": "LGPL-3.0+-or-later",
    "authors": [
        {
            "name": "Zikula",
            "homepage": "https://ziku.la/"
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
            "short-name": ""
        }
    }
}
EOF
        ;

        return json_decode($json, true);
    }
}
