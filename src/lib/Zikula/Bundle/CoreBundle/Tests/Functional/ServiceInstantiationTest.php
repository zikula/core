<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Tests\Functional;

/**
 * Make sure we instantiate services.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ServiceInstantiationTest extends BaseTestCase
{
    public function setUp()
    {
        static::createClient();
    }

    public function provider()
    {
        return [
            ['jms_translation.updater', 'JMS\TranslationBundle\Translation\Updater'],
            ['jms_translation.config_factory', 'JMS\TranslationBundle\Translation\ConfigFactory'],
            ['jms_translation.twig_extension', 'JMS\TranslationBundle\Twig\TranslationExtension'],
            ['twig.extension.zikula_gettext', 'Zikula\Bundle\CoreBundle\Twig\Extension\GettextExtension'],
            ['translator.default', 'Zikula\Common\Translator\Translator'],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testServiceExists($serviceId, $class)
    {
        $container = static::$kernel->getContainer();
        $this->assertTrue($container->has($serviceId));
        $service = $container->get($serviceId);
        $this->assertInstanceOf($class, $service);
    }
}
