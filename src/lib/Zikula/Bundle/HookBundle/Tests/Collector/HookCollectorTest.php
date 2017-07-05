<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Tests\Collector;

use Zikula\Bundle\HookBundle\Collector\HookCollector;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\HookSelfAllowedProviderInterface;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

class HookCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testAddProvider()
    {
        $collector = new HookCollector();
        $mockProvider = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider->method('getProviderTypes')->willReturn([]);
        $collector->addProvider('foo.areaName', 'foo.serviceName', $mockProvider);
        $this->assertTrue($collector->hasProvider('foo.areaName'));
    }

    public function testExceptionOnAddDuplicateProviderAreaName()
    {
        $collector = new HookCollector();
        $mockProvider1 = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider1->method('getProviderTypes')->willReturn([]);
        $mockProvider2 = clone $mockProvider1;
        $collector->addProvider('foo1.areaName', 'foo1.serviceName', $mockProvider1);

        $this->setExpectedException(\InvalidArgumentException::class);
        $collector->addProvider('foo1.areaName', 'foo2.serviceName', $mockProvider2);
    }

    public function testGetProvider()
    {
        $collector = new HookCollector();
        $mockProvider = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider->method('getProviderTypes')->willReturn([]);
        $collector->addProvider('foo.areaName', 'foo.serviceName', $mockProvider);
        $provider = $collector->getProvider('foo.areaName');
        $this->assertInstanceOf(HookProviderInterface::class, $provider);
    }

    public function testAddSubscriber()
    {
        $collector = new HookCollector();
        $mockSubscriber = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber->method('getEvents')->willReturn([]);
        $collector->addSubscriber('foo.areaName', $mockSubscriber);
        $this->assertTrue($collector->hasSubscriber('foo.areaName'));
    }

    public function testExceptionOnAddDuplicateSubscriberAreaName()
    {
        $collector = new HookCollector();
        $mockSubscriber1 = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber1->method('getEvents')->willReturn([]);
        $mockSubscriber2 = clone $mockSubscriber1;
        $collector->addSubscriber('foo1.areaName', $mockSubscriber1);

        $this->setExpectedException(\InvalidArgumentException::class);
        $collector->addSubscriber('foo1.areaName', $mockSubscriber2);
    }

    public function testGetSubscriber()
    {
        $collector = new HookCollector();
        $mockSubscriber = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber->method('getEvents')->willReturn([]);
        $collector->addSubscriber('foo.areaName', $mockSubscriber);
        $subsriber = $collector->getSubscriber('foo.areaName');
        $this->assertInstanceOf(HookSubscriberInterface::class, $subsriber);
    }

    public function testGetProviders()
    {
        $collector = new HookCollector();
        $mockProvider1 = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider1->method('getProviderTypes')->willReturn([]);
        $mockProvider2 = clone $mockProvider1;
        $mockProvider3 = clone $mockProvider1;
        $mockProvider1->method('getOwner')->willReturn('foo');
        $mockProvider2->method('getOwner')->willReturn('bar');
        $mockProvider3->method('getOwner')->willReturn('foo');
        $collector->addProvider('foo1.areaName', 'foo1.serviceName', $mockProvider1);
        $collector->addProvider('foo2.areaName', 'foo2.serviceName', $mockProvider2);
        $collector->addProvider('foo3.areaName', 'foo3.serviceName', $mockProvider3);
        $providers = $collector->getProviders();
        $this->assertCount(3, $providers);
        $this->assertEquals(['foo1.areaName', 'foo2.areaName', 'foo3.areaName'], $collector->getProviderAreas());
        $this->assertEquals(['foo1.areaName', 'foo3.areaName'], $collector->getProviderAreasByOwner('foo'));
        $this->assertEquals(['foo2.areaName'], $collector->getProviderAreasByOwner('bar'));
    }

    public function testGetSubscribers()
    {
        $collector = new HookCollector();
        $mockSubscriber1 = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber1->method('getEvents')->willReturn([]);
        $mockSubscriber2 = clone $mockSubscriber1;
        $mockSubscriber3 = clone $mockSubscriber1;
        $mockSubscriber1->method('getOwner')->willReturn('foo');
        $mockSubscriber2->method('getOwner')->willReturn('bar');
        $mockSubscriber3->method('getOwner')->willReturn('foo');
        $collector->addSubscriber('foo1.areaName', $mockSubscriber1);
        $collector->addSubscriber('foo2.areaName', $mockSubscriber2);
        $collector->addSubscriber('foo3.areaName', $mockSubscriber3);
        $subscribers = $collector->getSubscribers();
        $this->assertCount(3, $subscribers);
        $this->assertEquals(['foo1.areaName', 'foo2.areaName', 'foo3.areaName'], $collector->getSubscriberAreas());
        $this->assertEquals(['foo1.areaName', 'foo3.areaName'], $collector->getSubscriberAreasByOwner('foo'));
        $this->assertEquals(['foo2.areaName'], $collector->getSubscriberAreasByOwner('bar'));
    }

    public function testIsCapable()
    {
        $collector = new HookCollector();
        $mockSubscriber1 = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber1->method('getEvents')->willReturn([]);
        $mockSubscriber1->method('getOwner')->willReturn('foo');
        $collector->addSubscriber('foo.subscriber.areaName', $mockSubscriber1);
        $mockProvider1 = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider1->method('getProviderTypes')->willReturn([]);
        $mockProvider1->method('getOwner')->willReturn('foo');
        $collector->addProvider('foo.provider.areaName', 'foo.provider.serviceName', $mockProvider1);
        $mockProvider2 = $this->getMockBuilder(HookSelfAllowedProviderInterface::class)
            ->getMock();
        $mockProvider2->method('getProviderTypes')->willReturn([]);
        $mockProvider2->method('getOwner')->willReturn('bar');
        $collector->addProvider('foo.self.allowed.provider.areaName', 'foo.self.allowed.provider.serviceName', $mockProvider2);
        $this->assertTrue($collector->isCapable('foo', HookCollectorInterface::HOOK_SUBSCRIBER));
        $this->assertTrue($collector->isCapable('foo', HookCollectorInterface::HOOK_PROVIDER));
        $this->assertFalse($collector->isCapable('bar', HookCollectorInterface::HOOK_SUBSCRIBER));
        $this->assertTrue($collector->isCapable('bar', HookCollectorInterface::HOOK_PROVIDER));
        $this->assertTrue($collector->isCapable('bar', HookCollectorInterface::HOOK_SUBSCRIBE_OWN));

        $this->setExpectedException(\InvalidArgumentException::class);
        $collector->isCapable('foo', 'foo');
    }

    public function testGetOwnersCapableOf()
    {
        $collector = new HookCollector();
        $mockSubscriber1 = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber1->method('getEvents')->willReturn([]);
        $mockSubscriber1->method('getOwner')->willReturn('foo');
        $collector->addSubscriber('foo.subscriber.areaName', $mockSubscriber1);
        $mockProvider1 = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider1->method('getProviderTypes')->willReturn([]);
        $mockProvider1->method('getOwner')->willReturn('foo');
        $collector->addProvider('foo.provider.areaName', 'foo.provider.serviceName', $mockProvider1);
        $mockProvider2 = $this->getMockBuilder(HookSelfAllowedProviderInterface::class)
            ->getMock();
        $mockProvider2->method('getProviderTypes')->willReturn([]);
        $mockProvider2->method('getOwner')->willReturn('bar');
        $collector->addProvider('foo.self.allowed.provider.areaName', 'foo.self.allowed.provider.serviceName', $mockProvider2);
        $this->assertEquals(['foo'], $collector->getOwnersCapableOf(HookCollectorInterface::HOOK_SUBSCRIBER));
        $this->assertEquals(['foo', 'bar'], $collector->getOwnersCapableOf(HookCollectorInterface::HOOK_PROVIDER));

        $this->setExpectedException(\InvalidArgumentException::class);
        $collector->getOwnersCapableOf(HookCollectorInterface::HOOK_SUBSCRIBE_OWN);
        $collector->getOwnersCapableOf('foo');
    }
}
