<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel as Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

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
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new Symfony\Bundle\WorkflowBundle\WorkflowBundle(),
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
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
//            $bundles[] = new Zikula\Bundle\GeneratorBundle\ZikulaGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->rootDir.'/config/config_'.$this->getEnvironment().'.yml');
        $loader->load($this->rootDir.'/config/parameters.yml');
        if (is_readable($this->rootDir.'/config/custom_parameters.yml')) {
            $loader->load($this->rootDir.'/config/custom_parameters.yml');
        }

        if (!is_readable($this->rootDir . '/config/' . DynamicConfigDumper::CONFIG_GENERATED)) {
            // There is no generated configuration (yet), load default values.
            // This only happens at the very first time Symfony is started.
            $loader->load($this->rootDir . '/config/' . DynamicConfigDumper::CONFIG_DEFAULT);
        } else {
            $loader->load($this->rootDir . '/config/' . DynamicConfigDumper::CONFIG_GENERATED);
        }
    }
}
