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

namespace Zikula\ThemeModule\Tests\Api;

use PHPUnit\Framework\TestCase;
use Zikula\ThemeModule\Api\PageAssetApi;
use Zikula\ThemeModule\Engine\AssetBag;

class PageAssetApiTest extends TestCase
{
    /**
     * @var AssetBag
     */
    private $stylesheets;

    /**
     * @var PageAssetApi
     */
    private $api;

    protected function setUp(): void
    {
        $this->stylesheets = new AssetBag();
        $this->api = new PageAssetApi($this->stylesheets, new AssetBag(), new AssetBag(), new AssetBag());
    }

    public function testAdd(): void
    {
        $this->assertEmpty($this->stylesheets);
        $this->api->add('stylesheet', '/style.css');
        $this->assertCount(1, $this->stylesheets);
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testException($type, $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->api->add($type, $value);
    }

    public function exceptionDataProvider(): array
    {
        return [
            ['foo', 'bar'],
            ['', 'bar'],
            ['foo', ''],
        ];
    }
}
