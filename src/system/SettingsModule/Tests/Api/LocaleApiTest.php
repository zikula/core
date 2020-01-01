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

namespace Zikula\SettingsModule\Tests\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;
use Zikula\SettingsModule\Api\LocaleApi;

class LocaleApiTest extends TestCase
{
    public function testGetSupportedLocales(): void
    {
        $api = $this->getApi();
        $this->assertEquals(['en', 'ru', 'de'], $api->getSupportedLocales());
    }

    public function testGetSupportedLocaleNames(): void
    {
        $api = $this->getApi();
        $expected = [
            'English' => 'en',
            'German' => 'de',
            'Russian' => 'ru'
        ];
        $this->assertEquals($expected, $api->getSupportedLocaleNames(null, 'en'));
        $expected = [
            'Englisch' => 'en',
            'Deutsch' => 'de',
            'Russisch' => 'ru'
        ];
        $this->assertEquals($expected, $api->getSupportedLocaleNames(null, 'de'));
        $expected = [
            'inglese' => 'en',
            'tedesco' => 'de',
            'russo' => 'ru'
        ];
        $this->assertEquals($expected, $api->getSupportedLocaleNames(null, 'it'));
    }

    public function testEmpty(): void
    {
        $api = $this->getApi('');
        $this->assertEquals(['en'], $api->getSupportedLocales());
    }

    private function getApi(string $dir = '/Fixtures'): LocaleApiInterface
    {
        $kernel = $this->getMockBuilder(ZikulaHttpKernelInterface::class)->getMock();
        $kernel->method('getProjectDir')->willReturn(__DIR__ . $dir);
        $requestStack = $this->getMockBuilder(RequestStack::class)->getMock();

        return new LocaleApi($kernel, $requestStack);
    }
}
