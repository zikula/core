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

namespace Zikula\CoreBundle\Tests\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\CoreBundle\Api\LocaleApi;

#[CoversClass(LocaleApi::class)]
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
            'Inglese' => 'en',
            'Tedesco' => 'de',
            'Russo' => 'ru'
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
        $requestStack = $this->getMockBuilder(RequestStack::class)
            ->getMock()
        ;
        $projectDir = __DIR__ . $dir;

        return new LocaleApi($requestStack, $projectDir, true, 'en');
    }
}
