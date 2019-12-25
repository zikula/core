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
use Zikula\Bundle\CoreBundle\Bundle\Bootstrap;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel as Kernel;

class ZikulaKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        $bundles = [
            Symfony\Bundle\FrameworkBundle\FrameworkBundle::class,
            Symfony\Bundle\SecurityBundle\SecurityBundle::class,
            Symfony\Bundle\TwigBundle\TwigBundle::class,
            Symfony\Bundle\MonologBundle\MonologBundle::class,
            Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class,
            Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class,
            Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class,
            Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class,
            Zikula\Bundle\CoreBundle\CoreBundle::class,
            Zikula\Bundle\CoreInstallerBundle\ZikulaCoreInstallerBundle::class,
            Zikula\Bundle\FormExtensionBundle\ZikulaFormExtensionBundle::class,
            Zikula\Bundle\HookBundle\ZikulaHookBundle::class,
            Zikula\Bundle\JQueryBundle\ZikulaJQueryBundle::class,
            Zikula\Bundle\JQueryUIBundle\ZikulaJQueryUIBundle::class,
            JMS\I18nRoutingBundle\JMSI18nRoutingBundle::class,
            JMS\TranslationBundle\JMSTranslationBundle::class,
            FOS\JsRoutingBundle\FOSJsRoutingBundle::class,
            Matthias\SymfonyConsoleForm\Bundle\SymfonyConsoleFormBundle::class,
            Knp\Bundle\MenuBundle\KnpMenuBundle::class,
            Liip\ImagineBundle\LiipImagineBundle::class,
            Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle::class,
            Zikula\Bundle\WorkflowBundle\ZikulaWorkflowBundle::class,
        ];

        foreach (self::$coreModules as $bundleClass) {
            $bundles[] = $bundleClass;
        }
        $boot = new Bootstrap();
        $boot->getPersistedBundles($this, $bundles);

        if (in_array($this->getEnvironment(), ['dev', 'test'])) {
            $bundles[] = Symfony\Bundle\DebugBundle\DebugBundle::class;
            $bundles[] = Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class;
            $bundles[] = Elao\WebProfilerExtraBundle\WebProfilerExtraBundle::class;
            $bundles[] = Symfony\Bundle\MakerBundle\MakerBundle::class;
        }

        foreach ($bundles as $class) {
            yield new $class();
        }
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

    public function getProjectDir()
    {
        return dirname(__DIR__);
    }
}
