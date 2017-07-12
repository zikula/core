<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Tests\Api;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\SettingsModule\Api\LocaleApi;

class LocaleApiTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSupportedLocales()
    {
        $api = $this->getApi();
        $this->assertEquals(['en', 'de', 'ru'], $api->getSupportedLocales(false));
    }

    public function testGetSupportedLocaleNames()
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

    public function testEmpty()
    {
        $api = $this->getApi('');
        $this->assertEquals(['en'], $api->getSupportedLocales(false));
    }

    private function getApi($dir = '/Fixtures')
    {
        $kernel = $this->getMockBuilder(ZikulaHttpKernelInterface::class)->getMock();
        $kernel->method('getRootDir')->willReturn(__DIR__ . $dir);
        $requestStack = $this->getMockBuilder(RequestStack::class)->getMock();

        return new LocaleApi($kernel, $requestStack);
    }
}
