<?php
/**
 * Copyright 2015 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Tests\Api;

use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\ExtensionsModule\Api\HookApi;

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
        $this->api = new HookApi($translator);
    }

    public function testGetHookContainerInstance()
    {
        $meta = new MetaData($this->getJson());
        $hookContainerInstance = $this->api->getHookContainerInstance($meta);
        $this->assertInstanceOf('\Zikula\Component\HookDispatcher\AbstractContainer', $hookContainerInstance);
        $subscriberBundle = $hookContainerInstance->getHookSubscriberBundle('foo.area');

        $this->assertInstanceOf('\Zikula\Component\HookDispatcher\SubscriberBundle', $subscriberBundle);
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
                        "hook_subscriber" =>  ["class" =>  "Zikula\\ExtensionsModule\\Tests\\Api\\Fixtures\\HookContainer"]
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
