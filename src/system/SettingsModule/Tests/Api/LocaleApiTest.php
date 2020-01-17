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
use Zikula\SettingsModule\Helper\LocaleConfigHelper;

class LocaleApiTest extends TestCase
{
    public function testGetSupportedLocalesWithoutRegions(): void
    {
        $api = $this->getApi();
        $supportedLocales = $api->getSupportedLocales(false);
        foreach (['en', 'ru', 'de'] as $loc) {
            $this->assertContains($loc, $supportedLocales);
        }
        foreach (['de_DE'] as $loc) {
            $this->assertNotContains($loc, $supportedLocales);
        }
    }

    public function testGetSupportedLocalesWithRegions(): void
    {
        $api = $this->getApi();
        $supportedLocales = $api->getSupportedLocales(true);
        foreach (['en', 'ru', 'de', 'de_DE'] as $loc) {
            $this->assertContains($loc, $supportedLocales);
        }
    }

    public function testGetSupportedLocaleNames(): void
    {
        $api = $this->getApi();
        $expected = [
            'English' => 'en',
            'German' => 'de',
            'Russian' => 'ru'
        ];
        $this->assertEquals($expected, $api->getSupportedLocaleNames(null, 'en', false));
        $expected = [
            'Englisch' => 'en',
            'Deutsch' => 'de',
            'Russisch' => 'ru'
        ];
        $this->assertEquals($expected, $api->getSupportedLocaleNames(null, 'de', false));
        $expected = [
            'inglese' => 'en',
            'tedesco' => 'de',
            'russo' => 'ru'
        ];
        $this->assertEquals($expected, $api->getSupportedLocaleNames(null, 'it', false));
    }

    public function testGetSupportedLocaleNamesWithRegions(): void
    {
        $api = $this->getApi();
        $expected = [
            'English' => 'en',
            'German' => 'de',
            'German (Germany)' => 'de_DE',
            'Russian' => 'ru'
        ];
        $this->assertEquals($expected, $api->getSupportedLocaleNames(null, 'en', true));
        $expected = [
            'Englisch' => 'en',
            'Deutsch' => 'de',
            'Deutsch (Deutschland)' => 'de_DE',
            'Russisch' => 'ru'
        ];
        $this->assertEquals($expected, $api->getSupportedLocaleNames(null, 'de', true));
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
        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->getMock()
        ;
        $localeConfigHelper = $this->getMockBuilder(LocaleConfigHelper::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        return new LocaleApi($kernel, $requestStack, $localeConfigHelper, 'en', true);
    }
}
