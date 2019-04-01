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

namespace Zikula\Bundle\HookBundle\Tests\Collector;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Zikula\Bundle\HookBundle\Collector\HookCollector;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\HookSelfAllowedProviderInterface;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

class HookCollectorTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testAddProviderUsingConstructor(): void
    {
        $areaName = 'foo.areaName';
        $mockProvider = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider->method('getAreaName')->willReturn($areaName);
        $mockProvider->method('getProviderTypes')->willReturn([]);

        $collector = new HookCollector([$mockProvider]);
        $this->assertTrue($collector->hasProvider($areaName));
    }

    public function testAddProviderUsingMethod(): void
    {
        $areaName = 'foo.areaName';
        $mockProvider = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider->method('getAreaName')->willReturn($areaName);
        $mockProvider->method('getProviderTypes')->willReturn([]);

        $collector = new HookCollector();
        $collector->addProvider($mockProvider);
        $this->assertTrue($collector->hasProvider($areaName));
    }

    public function testExceptionOnAddDuplicateProviderAreaName(): void
    {
        $areaName = 'foo.areaName';
        $mockProvider1 = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider1->method('getAreaName')->willReturn($areaName);
        $mockProvider1->method('getProviderTypes')->willReturn([]);
        $mockProvider2 = clone $mockProvider1;

        $collector = new HookCollector();
        $collector->addProvider($mockProvider1);

        $this->expectException(InvalidArgumentException::class);
        $collector->addProvider($mockProvider2);
    }

    public function testGetProvider(): void
    {
        $areaName = 'foo.areaName';
        $mockProvider = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider->method('getAreaName')->willReturn($areaName);
        $mockProvider->method('getProviderTypes')->willReturn([]);

        $collector = new HookCollector();
        $collector->addProvider($mockProvider);
        $provider = $collector->getProvider($areaName);
        $this->assertInstanceOf(HookProviderInterface::class, $provider);
    }

    public function testAddSubscriberUsingConstructor(): void
    {
        $areaName = 'foo.areaName';
        $mockSubscriber = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber->method('getAreaName')->willReturn($areaName);
        $mockSubscriber->method('getEvents')->willReturn([]);

        $collector = new HookCollector([], [$mockSubscriber]);
        $this->assertTrue($collector->hasSubscriber($areaName));
    }

    public function testAddSubscriberUsingMethod(): void
    {
        $areaName = 'foo.areaName';
        $mockSubscriber = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber->method('getAreaName')->willReturn($areaName);
        $mockSubscriber->method('getEvents')->willReturn([]);

        $collector = new HookCollector();
        $collector->addSubscriber($mockSubscriber);
        $this->assertTrue($collector->hasSubscriber($areaName));
    }

    public function testExceptionOnAddDuplicateSubscriberAreaName(): void
    {
        $areaName = 'foo.areaName';
        $mockSubscriber1 = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber1->method('getAreaName')->willReturn($areaName);
        $mockSubscriber1->method('getEvents')->willReturn([]);
        $mockSubscriber2 = clone $mockSubscriber1;

        $collector = new HookCollector();
        $collector->addSubscriber($mockSubscriber1);

        $this->expectException(InvalidArgumentException::class);
        $collector->addSubscriber($mockSubscriber2);
    }

    public function testGetSubscriber(): void
    {
        $areaName = 'foo.areaName';
        $mockSubscriber = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber->method('getAreaName')->willReturn($areaName);
        $mockSubscriber->method('getEvents')->willReturn([]);

        $collector = new HookCollector();
        $collector->addSubscriber($mockSubscriber);
        $subscriber = $collector->getSubscriber($areaName);
        $this->assertInstanceOf(HookSubscriberInterface::class, $subscriber);
    }

    public function testGetProviders(): void
    {
        $mockProvider1 = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider1->method('getProviderTypes')->willReturn([]);
        $mockProvider2 = clone $mockProvider1;
        $mockProvider3 = clone $mockProvider1;
        $mockProvider1->method('getAreaName')->willReturn('foo1.areaName');
        $mockProvider2->method('getAreaName')->willReturn('foo2.areaName');
        $mockProvider3->method('getAreaName')->willReturn('foo3.areaName');
        $mockProvider1->method('getOwner')->willReturn('foo');
        $mockProvider2->method('getOwner')->willReturn('bar');
        $mockProvider3->method('getOwner')->willReturn('foo');

        $collector = new HookCollector([$mockProvider1, $mockProvider2, $mockProvider3]);
        $providers = $collector->getProviders();
        $this->assertCount(3, $providers);
        $this->assertEquals(['foo1.areaName', 'foo2.areaName', 'foo3.areaName'], $collector->getProviderAreas());
        $this->assertEquals(['foo1.areaName', 'foo3.areaName'], $collector->getProviderAreasByOwner('foo'));
        $this->assertEquals(['foo2.areaName'], $collector->getProviderAreasByOwner('bar'));
    }

    public function testGetSubscribers(): void
    {
        $mockSubscriber1 = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber1->method('getEvents')->willReturn([]);
        $mockSubscriber2 = clone $mockSubscriber1;
        $mockSubscriber3 = clone $mockSubscriber1;
        $mockSubscriber1->method('getAreaName')->willReturn('foo1.areaName');
        $mockSubscriber2->method('getAreaName')->willReturn('foo2.areaName');
        $mockSubscriber3->method('getAreaName')->willReturn('foo3.areaName');
        $mockSubscriber1->method('getOwner')->willReturn('foo');
        $mockSubscriber2->method('getOwner')->willReturn('bar');
        $mockSubscriber3->method('getOwner')->willReturn('foo');

        $collector = new HookCollector([], [$mockSubscriber1, $mockSubscriber2, $mockSubscriber3]);
        $subscribers = $collector->getSubscribers();
        $this->assertCount(3, $subscribers);
        $this->assertEquals(['foo1.areaName', 'foo2.areaName', 'foo3.areaName'], $collector->getSubscriberAreas());
        $this->assertEquals(['foo1.areaName', 'foo3.areaName'], $collector->getSubscriberAreasByOwner('foo'));
        $this->assertEquals(['foo2.areaName'], $collector->getSubscriberAreasByOwner('bar'));
    }

    public function testIsCapable(): void
    {
        $mockSubscriber1 = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber1->method('getAreaName')->willReturn('foo.subscriber.areaName');
        $mockSubscriber1->method('getEvents')->willReturn([]);
        $mockSubscriber1->method('getOwner')->willReturn('foo');

        $mockProvider1 = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider1->method('getAreaName')->willReturn('foo.provider.areaName');
        $mockProvider1->method('getProviderTypes')->willReturn([]);
        $mockProvider1->method('getOwner')->willReturn('foo');

        $mockProvider2 = $this->getMockBuilder(HookSelfAllowedProviderInterface::class)
            ->getMock();
        $mockProvider2->method('getAreaName')->willReturn('foo.self.allowed.provider.areaName');
        $mockProvider2->method('getProviderTypes')->willReturn([]);
        $mockProvider2->method('getOwner')->willReturn('bar');

        $collector = new HookCollector();
        $collector->addSubscriber($mockSubscriber1);
        $collector->addProvider($mockProvider1);
        $collector->addProvider($mockProvider2);
        $this->assertTrue($collector->isCapable('foo', HookCollectorInterface::HOOK_SUBSCRIBER));
        $this->assertTrue($collector->isCapable('foo', HookCollectorInterface::HOOK_PROVIDER));
        $this->assertFalse($collector->isCapable('bar', HookCollectorInterface::HOOK_SUBSCRIBER));
        $this->assertTrue($collector->isCapable('bar', HookCollectorInterface::HOOK_PROVIDER));
        $this->assertTrue($collector->isCapable('bar', HookCollectorInterface::HOOK_SUBSCRIBE_OWN));

        $this->expectException(InvalidArgumentException::class);
        $collector->isCapable('foo', 'foo');
    }

    public function testGetOwnersCapableOf(): void
    {
        $mockSubscriber1 = $this->getMockBuilder(HookSubscriberInterface::class)
            ->getMock();
        $mockSubscriber1->method('getAreaName')->willReturn('foo.subscriber.areaName');
        $mockSubscriber1->method('getEvents')->willReturn([]);
        $mockSubscriber1->method('getOwner')->willReturn('foo');

        $mockProvider1 = $this->getMockBuilder(HookProviderInterface::class)
            ->getMock();
        $mockProvider1->method('getAreaName')->willReturn('foo.provider.areaName');
        $mockProvider1->method('getProviderTypes')->willReturn([]);
        $mockProvider1->method('getOwner')->willReturn('foo');

        $mockProvider2 = $this->getMockBuilder(HookSelfAllowedProviderInterface::class)
            ->getMock();
        $mockProvider2->method('getAreaName')->willReturn('foo.self.allowed.provider.areaName');
        $mockProvider2->method('getProviderTypes')->willReturn([]);
        $mockProvider2->method('getOwner')->willReturn('bar');

        $collector = new HookCollector();
        $collector->addSubscriber($mockSubscriber1);
        $collector->addProvider($mockProvider1);
        $collector->addProvider($mockProvider2);
        $this->assertEquals(['foo'], $collector->getOwnersCapableOf(HookCollectorInterface::HOOK_SUBSCRIBER));
        $this->assertEquals(['foo', 'bar'], $collector->getOwnersCapableOf(HookCollectorInterface::HOOK_PROVIDER));

        $this->expectException(InvalidArgumentException::class);
        $collector->getOwnersCapableOf(HookCollectorInterface::HOOK_SUBSCRIBE_OWN);
        $collector->getOwnersCapableOf('foo');
    }
}
