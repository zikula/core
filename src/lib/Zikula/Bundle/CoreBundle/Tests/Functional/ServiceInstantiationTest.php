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

namespace Zikula\Bundle\CoreBundle\Tests\Functional;

use JMS\TranslationBundle\Translation\ConfigFactory;
use JMS\TranslationBundle\Translation\Updater;
use JMS\TranslationBundle\Twig\TranslationExtension;

/**
 * Make sure we instantiate services.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ServiceInstantiationTest extends BaseTestCase
{
    protected function setUp(): void
    {
        static::createClient();
    }

    public function provider(): array
    {
        return [
            ['jms_translation.updater', Updater::class],
            ['jms_translation.config_factory', ConfigFactory::class],
            ['jms_translation.twig_extension', TranslationExtension::class]
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testServiceExists(string $serviceId, string $class): void
    {
        $container = static::$kernel->getContainer();
        $this->assertTrue($container->has($serviceId));
        $service = $container->get($serviceId);
        $this->assertInstanceOf($class, $service);
    }
}
