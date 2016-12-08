<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Tests\Api;

use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\HookBundle\Api\HookApi;

class HookApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HookApi
     */
    private $api;

    public function setUp()
    {
        $translator = $this
            ->getMockBuilder('\Zikula\Common\Translator\Translator')
            ->disableOriginalConstructor()
            ->getMock();
        $translator
            ->method('__')
            ->willReturnArgument(0);
        $hookDispatcher = $this
            ->getMockBuilder('\Zikula\Bundle\HookBundle\Dispatcher\HookDispatcher')
            ->disableOriginalConstructor()
            ->getMock();
        $eventDispatcher = $this
            ->getMockBuilder('\Zikula_EventManager') // @TODO change to Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
            ->disableOriginalConstructor()
            ->getMock();
        $this->api = new HookApi($translator, $hookDispatcher, $eventDispatcher);
    }

    /**
     * @covers \HookApi::getHookContainerInterface
     */
    public function testGetHookContainerInstance()
    {
        $meta = new MetaData($this->getJson());
        $hookContainerInstance = $this->api->getHookContainerInstance($meta);
        $this->assertInstanceOf('\Zikula\Bundle\HookBundle\AbstractHookContainer', $hookContainerInstance);
        $subscriberBundle = $hookContainerInstance->getHookSubscriberBundle('foo.area');

        $this->assertInstanceOf('\Zikula\Bundle\HookBundle\Bundle\SubscriberBundle', $subscriberBundle);
        $this->assertEquals('Translatable title', $subscriberBundle->getTitle());
        $this->assertEquals('ui_hooks', $subscriberBundle->getCategory());
        $this->assertEquals('foo.area', $subscriberBundle->getArea());

        $subscriberHookContainerInstance = $this->api->getHookContainerInstance($meta, HookApi::SUBSCRIBER_TYPE);
        $this->assertEquals($hookContainerInstance, $subscriberHookContainerInstance);

        $this->assertEmpty($hookContainerInstance->getHookProviderBundles());

        $hookEvents = $subscriberBundle->getEvents();
        $this->assertCount(1, $hookEvents);
        $this->assertEquals('foo.event.name', $hookEvents['form_edit']);
    }

    private function getJson()
    {
        $jsonArray = [
            "name" =>  "zikula/specmodule-module",
            "version" =>  "2.0.0-beta",
            "description" =>  "A module depicting the Core-2.0.0 Extension specification.",
            "type" =>  "zikula-module",
            "license" =>  "MIT",
            "authors" =>  [
                "name" =>  "Zikula Team",
                "homepage" =>  "http => //zikula.org/"
            ],
            "autoload" =>  [
                "psr-4" =>  ["Zikula\\SpecModule\\" =>  ""]
            ],
            "require" =>  [
                "php" =>  ">=5.4.1"
            ],
            "extra" =>  [
                "zikula" =>  [
                    "core-compatibility" =>  ">=1.4.1",
                    "class" =>  "Zikula\\SpecModule\\ZikulaSpecModule",
                    "displayname" =>  "SpecModule",
                    "url" =>  "spec",
                    "oldnames" =>  [],
                    "capabilities" =>  [
                        "hook_subscriber" =>  ["class" =>  "Zikula\\Bundle\\HookBundle\\Tests\\Api\\Fixtures\\HookContainer"]
                    ],
                    "securityschema" =>  [
                        "ZikulaSpecModule::" => "::"
                    ],
                    "base-path" => "",
                    "root-path" => "",
                    "short-name" => "SpecModule",
                    "extensionType" => "system"
                ]
            ]
        ];

        return json_decode(json_encode($jsonArray), true);
    }
}
