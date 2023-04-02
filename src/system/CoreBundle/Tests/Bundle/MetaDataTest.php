<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CoreBundle\Tests\Bundle;

use PHPUnit\Framework\TestCase;
use Zikula\AdminBundle\ZikulaAdminBundle;
use Zikula\CoreBundle\Composer\MetaData;

class MetaDataTest extends TestCase
{
    protected MetaData $metaData;

    protected array $json;

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
        $this->assertEquals('zikula/admin-bundle', $this->metaData->getName());
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
        $this->assertEquals(ZikulaAdminBundle::class, $this->metaData->getClass());
    }

    /**
     * @covers MetaData::getNamespace
     */
    public function testGetNamespace(): void
    {
        $this->assertEquals('Zikula\\AdminBundle\\', $this->metaData->getNamespace());
    }

    private function getJson(): array
    {
        $json = <<<'EOF'
{
    "name": "zikula/admin-bundle",
    "description": "Backend administration interface",
    "type": "symfony-bundle",
    "license": "LGPL-3.0+-or-later",
    "authors": [
        {
            "name": "Zikula",
            "homepage": "https://ziku.la/"
        }
    ],
    "autoload": {
        "psr-0": { "Zikula\\AdminBundle\\": "" }
    },
    "require": {
        "php": ">5.4.1"
    },
    "extra": {
        "zikula": {
            "class": "Zikula\\AdminBundle\\ZikulaAdminBundle"
        }
    }
}
EOF
        ;

        return json_decode($json, true);
    }
}
