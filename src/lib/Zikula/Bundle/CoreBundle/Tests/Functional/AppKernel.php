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

use JMS\TranslationBundle\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;

class AppKernel extends ZikulaKernel
{
    private $config;

    public function __construct($config)
    {
        parent::__construct('test', true);

        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($config)) {
            $config = __DIR__.'/config/'.$config;
        }

        if (!file_exists($config)) {
            throw new RuntimeException(sprintf('The config file "%s" does not exist.', $config));
        }

        $this->config = $config;
    }

    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
            new \JMS\TranslationBundle\JMSTranslationBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Zikula\Bundle\CoreBundle\Tests\Functional\Fixture\TestBundle\TestBundle(), // contains translation.xml config definitions
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->config);
    }

    public function serialize()
    {
        return $this->config;
    }

    public function unserialize($config)
    {
        $this->__construct($config);
    }

    /**
     * This needs to be set to the 'normal' kernel cache dir
     * @return string
     */
    public function getCacheDir()
    {
        return __DIR__ . '/../../../../../../var/cache/test';
    }

    /*
     * This needs to be set to the 'normal' kernel logs dir
     */
    public function getLogDir()
    {
        return __DIR__ . '/../../../../../../var/logs';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException if a custom resource is hidden by a resource in a derived bundle
     */
    public function locateResource($name, $dir = null, $first = true)
    {
        return Kernel::locateResource($name, $dir, $first);
    }
}
