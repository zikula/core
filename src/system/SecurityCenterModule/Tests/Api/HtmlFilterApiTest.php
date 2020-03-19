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

namespace Zikula\SecurityCenterModule\Tests\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\SecurityCenterModule\Api\ApiInterface\HtmlFilterApiInterface;
use Zikula\SecurityCenterModule\Api\HtmlFilterApi;
use Zikula\SecurityCenterModule\Tests\Api\Fixtures\FilterTestSubscriber;

class HtmlFilterApiTest extends TestCase
{
    /**
     * @var array
     */
    private $allowableHTML = [];

    public function testAllowedTags(): void
    {
        $this->allowableHTML = [
            'i' => HtmlFilterApiInterface::TAG_NOT_ALLOWED,
            'strong' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'div' => HtmlFilterApiInterface::TAG_ALLOWED_PLAIN,
            'h3' => HtmlFilterApiInterface::TAG_ALLOWED_WITH_ATTRIBUTES,
        ];
        $api = $this->getApi();
        $string = 'foo <i>bar</i> <strong>bold</strong> blue <div>div without attributes</div><div class="text-center">div with class attribute</div><h3 class="text-center">h3 with attributes</h3>';
        $expected = 'foo &lt;i&gt;bar&lt;/i&gt; <strong>bold</strong> blue <div>div without attributes</div>&lt;div class=&quot;text-center&quot;&gt;div with class attribute</div><h3 class="text-center">h3 with attributes</h3>';
        $this->assertEquals($expected, $api->filter($string));
    }

    /**
     * @dataProvider stringProvider
     */
    public function testHtmlEntities(string $string, string $expectedOn, string $exptectedOff): void
    {
        $api = $this->getApi();
        $this->assertEquals($expectedOn, $api->filter($string));
        $api = $this->getApi(false);
        $this->assertEquals($exptectedOff, $api->filter($string));
    }

    public function stringProvider(): array
    {
        return [
            ['"foo" and \'bar\' <foo>', '&quot;foo&quot; and \'bar\' &lt;foo&gt;', '&quot;foo&quot; and \'bar\' &lt;foo&gt;'],
            ['foo &amp;xyz;bar', 'foo &amp;xyz;bar', 'foo &amp;amp;xyz;bar'], // `&` is converted then converted back
        ];
    }

    public function testSubscriber(): void
    {
        $api = $this->getApi(true, true);
        $this->assertEquals('***foo***', $api->filter('foo'));
    }

    private function getApi(bool $htmlEntities = true, bool $outputFilter = false): HtmlFilterApiInterface
    {
        $variableApi = $this->getMockBuilder(VariableApiInterface::class)->getMock();
        $variableApi->method('getSystemVar')->willReturnCallback(
            function($string, $default) use ($htmlEntities, $outputFilter) {
                switch ($string) {
                    case 'outputfilter':
                        return $outputFilter;
                    case 'htmlentities':
                        return $htmlEntities;
                    case 'AllowableHTML':
                        return $this->getAllowableHTML();
                    default:
                        return $default;
                }
            }
        );
        $eventDispatcher = new EventDispatcher();
        if ($outputFilter) {
            $subscriber = new FilterTestSubscriber();
            $eventDispatcher->addSubscriber($subscriber);
        }

        return new HtmlFilterApi($variableApi, '3.0.0', $eventDispatcher);
    }

    private function getAllowableHTML(): array
    {
        return $this->allowableHTML;
    }
}
