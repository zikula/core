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

namespace Zikula\ThemeModule\Tests\Api;

use Zikula\ThemeModule\Api\PageAssetApi;
use Zikula\ThemeModule\Engine\AssetBag;

class PageAssetApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AssetBag
     */
    private $stylesheets;

    /**
     * @var PageAssetApi
     */
    private $api;

    protected function setUp()
    {
        $this->stylesheets = new AssetBag();
        $this->api = new PageAssetApi($this->stylesheets, new AssetBag(), new AssetBag(), new AssetBag());
    }

    public function testAdd()
    {
        $this->assertEmpty($this->stylesheets);
        $this->api->add('stylesheet', '/style.css');
        $this->assertCount(1, $this->stylesheets);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider exceptionDataProvider
     */
    public function testException($type, $value)
    {
        $this->api->add($type, $value);
    }

    public function exceptionDataProvider()
    {
        return [
            ['foo', 'bar'],
            ['', 'bar'],
            ['foo', ''],
        ];
    }
}
