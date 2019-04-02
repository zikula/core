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

namespace Zikula\ExtensionsModule\Tests\Api;

use PHPUnit\Framework\TestCase;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Api\CapabilityApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Tests\Api\Fixtures\ExtensionStubRepository;

class CapabilityApiTest extends TestCase
{
    /**
     * @var CapabilityApiInterface
     */
    private $api;

    protected function setUp(): void
    {
        $repo = new ExtensionStubRepository();
        $this->api = new CapabilityApi($repo);
    }

    /**
     * @dataProvider getExtensionsCapableOfProvider
     * @covers CapabilityApi::getExtensionsCapableOf
     */
    public function testGetExtensionsCapableOf(string $type, int $count, array $names): void
    {
        $extensions = $this->api->getExtensionsCapableOf($type);
        $this->assertCount($count, $extensions);
        foreach ($extensions as $extension) {
            $this->assertInstanceOf(ExtensionEntity::class, $extension);
            $this->assertContains($extension->getName(), $names);
        }
    }

    /**
     * @covers CapabilityApi::isCapable
     */
    public function testIsCapable(): void
    {
        $this->assertNotFalse($this->api->isCapable('FooExtension', CapabilityApiInterface::ADMIN));
        $this->assertFalse($this->api->isCapable('FooExtension', CapabilityApiInterface::USER));
        $this->assertFalse($this->api->isCapable('FooExtension', CapabilityApiInterface::CATEGORIZABLE));
    }

    /**
     * @covers CapabilityApi::getCapabilitiesOf
     */
    public function testGetCapabilitiesOf(): void
    {
        $capabilities = $this->api->getCapabilitiesOf('BarExtension');
        $this->assertCount(2, $capabilities);
        $this->assertEquals([
            CapabilityApiInterface::ADMIN => ['route' => 'bar_admin_route'],
            CapabilityApiInterface::USER => ['route' => 'bar_user_route'],
        ], $capabilities);

        $capabilities = $this->api->getCapabilitiesOf('NoneExtension');
        $this->assertCount(0, $capabilities);
    }

    public function getExtensionsCapableOfProvider(): array
    {
        return [
            [CapabilityApiInterface::ADMIN, 3, ['FooExtension', 'BarExtension', 'BazExtension']],
            [CapabilityApiInterface::USER, 1, ['BarExtension']],
        ];
    }
}
