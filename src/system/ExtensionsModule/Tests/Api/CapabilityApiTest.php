<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Tests\Api;

use Zikula\ExtensionsModule\Api\CapabilityApi;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Tests\Api\Fixtures\ExtensionStubRepository;

class CapabilityApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CapabilityApi
     */
    private $api;

    /**
     * CapabilityApiTest constructor.
     */
    public function setUp()
    {
        $repo = new ExtensionStubRepository();
        $this->api = new CapabilityApi($repo);
    }

    /**
     * @dataProvider getExtensionsCapableOfProvider
     * @covers  CapabilityApi::getExtensionsCapableOf
     * @param $type
     * @param $count
     * @param $names
     */
    public function testGetExtensionsCapableOf($type, $count, $names)
    {
        $extensions = $this->api->getExtensionsCapableOf($type);
        $this->assertCount($count, $extensions);
        foreach ($extensions as $extension) {
            $this->assertInstanceOf('Zikula\ExtensionsModule\Entity\ExtensionEntity', $extension);
            $this->assertContains($extension->getName(), $names);
        }
    }

    /**
     * @covers CapabilityApi::isCapable
     */
    public function testIsCapable()
    {
        $this->assertNotFalse($this->api->isCapable('FooExtension', CapabilityApiInterface::ADMIN));
        $this->assertNotFalse($this->api->isCapable('BazExtension', CapabilityApiInterface::SEARCHABLE));
        $this->assertNotFalse($this->api->isCapable('FazExtension', CapabilityApiInterface::HOOK_SUBSCRIBER));
        $this->assertNotFalse($this->api->isCapable('FazExtension', CapabilityApiInterface::HOOK_SUBSCRIBE_OWN));
        $this->assertFalse($this->api->isCapable('FooExtension', CapabilityApiInterface::USER));
        $this->assertFalse($this->api->isCapable('FooExtension', CapabilityApiInterface::CATEGORIZABLE));
    }

    /**
     * @covers CapabilityApi::getCapabilitiesOf
     */
    public function testGetCapabilitiesOf()
    {
        $e = $this->api->getCapabilitiesOf('BarExtension');
        $this->assertTrue(is_array($e));
        $this->assertCount(3, $e);
        $this->assertEquals([
            CapabilityApiInterface::ADMIN => ['route' => 'bar_admin_route'],
            CapabilityApiInterface::USER => ['route' => 'bar_user_route'],
            CapabilityApiInterface::SEARCHABLE => ['class' => 'Acme\\BarExtension\\Search']
        ], $e);
        $e = $this->api->getCapabilitiesOf('NoneExtension');
        $this->assertTrue(is_array($e));
        $this->assertCount(0, $e);
    }

    public function getExtensionsCapableOfProvider()
    {
        return [
            [CapabilityApiInterface::ADMIN, 3, ['FooExtension', 'BarExtension', 'BazExtension']],
            [CapabilityApiInterface::USER, 1, ['BarExtension']],
            [CapabilityApiInterface::SEARCHABLE, 2, ['BarExtension', 'BazExtension']],
        ];
    }
}
