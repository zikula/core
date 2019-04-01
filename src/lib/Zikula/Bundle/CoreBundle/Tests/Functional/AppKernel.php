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

use JMS\I18nRoutingBundle\JMSI18nRoutingBundle;
use JMS\TranslationBundle\JMSTranslationBundle;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\Tests\Functional\Fixture\TestBundle\TestBundle;

class AppKernel extends ZikulaKernel
{
    /**
     * @var string
     */
    private $config;

    public function __construct(string $config)
    {
        parent::__construct('test', true);

        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($config)) {
            $config = __DIR__ . '/config/' . $config;
        }

        if (!file_exists($config)) {
            throw new RuntimeException(sprintf('The config file "%s" does not exist.', $config));
        }

        $this->config = $config;
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new JMSI18nRoutingBundle(),
            new JMSTranslationBundle(),
            new SensioFrameworkExtraBundle(),
            new TestBundle(), // contains translation.xml config definitions
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->config);
    }

    public function serialize(): string
    {
        return $this->config;
    }

    public function unserialize($config): void
    {
        $this->__construct($config);
    }

    /**
     * This needs to be set to the 'normal' kernel cache dir
     */
    public function getCacheDir(): string
    {
        return __DIR__ . '/../../../../../../var/cache/test';
    }

    /*
     * This needs to be set to the 'normal' kernel logs dir
     */
    public function getLogDir(): string
    {
        return __DIR__ . '/../../../../../../var/logs';
    }
}
