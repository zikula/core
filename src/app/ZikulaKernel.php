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

use Symfony\Component\Config\Loader\LoaderInterface;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel as Kernel;

class ZikulaKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Zikula\Bundle\CoreBundle\CoreBundle(),
            new Zikula\Bundle\CoreInstallerBundle\ZikulaCoreInstallerBundle(),
            new Zikula\Bundle\FormExtensionBundle\ZikulaFormExtensionBundle(),
            new Zikula\Bundle\HookBundle\ZikulaHookBundle(),
            new Zikula\Bundle\JQueryBundle\ZikulaJQueryBundle(),
            new Zikula\Bundle\JQueryUIBundle\ZikulaJQueryUIBundle(),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Matthias\SymfonyConsoleForm\Bundle\SymfonyConsoleFormBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
//            new Liip\ImagineBundle\LiipImagineBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new Zikula\Bundle\WorkflowBundle\ZikulaWorkflowBundle(),
        ];

        foreach (self::$coreModules as $bundleClass) {
            $bundles[] = new $bundleClass();
        }
        $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
        $boot->getPersistedBundles($this, $bundles);

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Elao\WebProfilerExtraBundle\WebProfilerExtraBundle();
            $bundles[] = new Zikula\Bundle\GeneratorBundle\ZikulaGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $configDir = $this->getProjectDir() . '/app/config/';
        $loader->load($configDir . 'config_' . $this->getEnvironment() . '.yml');

        $loader->load($configDir . 'parameters.yml');
        if (is_readable($configDir . 'custom_parameters.yml')) {
            $loader->load($configDir . 'custom_parameters.yml');
        }

        if (!is_readable($configDir . DynamicConfigDumper::CONFIG_GENERATED)) {
            // There is no generated configuration (yet), load default values.
            // This only happens at the very first time Symfony is started.
            $loader->load($configDir . DynamicConfigDumper::CONFIG_DEFAULT);
        } else {
            $loader->load($configDir . DynamicConfigDumper::CONFIG_GENERATED);
        }
    }

    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/' . $this->environment;
    }

    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/logs';
    }

    public function getProjectDir()
    {
        return dirname(__DIR__);
    }
}
