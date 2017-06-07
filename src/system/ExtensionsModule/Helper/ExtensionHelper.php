<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Helper;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bundle\CoreBundle\Console\Application;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\AbstractBundle;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\Core\ExtensionInstallerInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\ExtensionEvents;

class ExtensionHelper
{
    const TYPE_SYSTEM = 3;
    const TYPE_MODULE = 2;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ExtensionHelper constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $container->get('translator.default');
        $this->translator->setLocale('ZikulaExtensionsModule');
    }

    /**
     * Install an extension.
     *
     * @param ExtensionEntity $extension
     * @return bool
     */
    public function install(ExtensionEntity $extension)
    {
        if ($extension->getState() == Constant::STATE_NOTALLOWED) {
            throw new \RuntimeException($this->translator->__f('Error! No permission to install %s.', ['%s' => $extension->getName()]));
        } elseif ($extension->getState() > 10) {
            throw new \RuntimeException($this->translator->__f('Error! %s is not compatible with this version of Zikula.', ['%s' => $extension->getName()]));
        }

        $bundle = $this->container->get('kernel')->getBundle($extension->getName());

        $installer = $this->getExtensionInstallerInstance($bundle);
        $result = $installer->install();
        if (!$result) {
            return false;
        }
        $this->container->get('zikula_extensions_module.extension_state_helper')->updateState($extension->getId(), Constant::STATE_ACTIVE);

        // clear the cache before calling events
        /** @var $cacheClearer \Zikula\Bundle\CoreBundle\CacheClearer */
        $cacheClearer = $this->container->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony.config');

        $event = new ModuleStateEvent($bundle, $extension->toArray());
        $this->container->get('event_dispatcher')->dispatch(CoreEvents::MODULE_INSTALL, $event);

        return true;
    }

    /**
     * Upgrade an extension.
     *
     * @param ExtensionEntity $extension
     * @return bool
     */
    public function upgrade(ExtensionEntity $extension)
    {
        switch ($extension->getState()) {
            case Constant::STATE_NOTALLOWED:
                throw new \RuntimeException($this->translator->__f('Error! Not allowed to upgrade %s.', ['%s' => $extension->getDisplayname()]));
                break;
            default:
                if ($extension->getState() > 10) {
                    throw new \RuntimeException($this->translator->__f('Error! %s is not compatible with this version of Zikula.', ['%s' => $extension->getDisplayname()]));
                }
        }

        $bundle = $this->container->get('kernel')->getModule($extension->getName());

        // @TODO: Need to check status of Dependencies here to be sure they are met for upgraded extension.

        $installer = $this->getExtensionInstallerInstance($bundle);
        $result = $installer->upgrade($extension->getVersion());
        if (is_string($result)) {
            if ($result != $extension->getVersion()) {
                // persist the last successful updated version
                $extension->setVersion($result);
                $this->container->get('doctrine')->getManager()->flush();
            }

            return false;
        } elseif (true !== $result) {
            return false;
        }
        // persist the updated version
        $newVersion = $bundle->getMetaData()->getVersion();
        $extension->setVersion($newVersion);
        $this->container->get('doctrine')->getManager()->flush();

        $this->container->get('zikula_extensions_module.extension_state_helper')->updateState($extension->getId(), Constant::STATE_ACTIVE);

        $this->container->get('zikula.cache_clearer')->clear('symfony');

        if ($this->container->getParameter('installed')) {
            // Upgrade succeeded, issue event.
            $event = new ModuleStateEvent($bundle, $extension->toArray());
            $this->container->get('event_dispatcher')->dispatch(CoreEvents::MODULE_UPGRADE, $event);
        }

        return true;
    }

    /**
     * Uninstall an extension.
     *
     * @param ExtensionEntity $extension
     * @return bool
     */
    public function uninstall(ExtensionEntity $extension)
    {
        if ($extension->getState() == Constant::STATE_NOTALLOWED
            || (ZikulaKernel::isCoreModule($extension->getName()))) {
            throw new \RuntimeException($this->translator->__f('Error! No permission to uninstall %s.', ['%s' => $extension->getDisplayname()]));
        }
        if ($extension->getState() == Constant::STATE_UNINITIALISED) {
            throw new \RuntimeException($this->translator->__f('Error! %s is not yet installed, therefore it cannot be uninstalled.', ['%s' => $extension->getDisplayname()]));
        }

        // allow event to prevent extension removal
        $vetoEvent = new GenericEvent($extension);
        $this->container->get('event_dispatcher')->dispatch(ExtensionEvents::REMOVE_VETO, $vetoEvent);
        if ($vetoEvent->isPropagationStopped()) {
            return false;
        }

        $bundle = $this->container->get('kernel')->getBundle($extension->getName());

        // remove hooks
        $this->container->get('zikula_hook_bundle.api.hook')->uninstallProviderHooks($bundle->getMetaData());
        $this->container->get('zikula_hook_bundle.api.hook')->uninstallSubscriberHooks($bundle->getMetaData());

        $installer = $this->getExtensionInstallerInstance($bundle);
        $result = $installer->uninstall();
        if (!$result) {
            return false;
        }

        // remove remaining extension variables
        $this->container->get('zikula_extensions_module.api.variable')->delAll($extension->getName());

        // remove the entry from the modules table
        $this->container->get('doctrine')->getManager()->getRepository('ZikulaExtensionsModule:ExtensionEntity')->removeAndFlush($extension);

        // clear the cache before calling events
        /** @var $cacheClearer \Zikula\Bundle\CoreBundle\CacheClearer */
        $cacheClearer = $this->container->get('zikula.cache_clearer');
        $cacheClearer->clear('symfony.config');

        $event = new ModuleStateEvent($bundle, $extension->toArray());
        $this->container->get('event_dispatcher')->dispatch(CoreEvents::MODULE_REMOVE, $event);

        return true;
    }

    /**
     * Uninstall an array of extensions.
     *
     * @param ExtensionEntity[] $extensions
     * @return bool
     */
    public function uninstallArray(array $extensions)
    {
        foreach ($extensions as $extension) {
            if (!$extension instanceof ExtensionEntity) {
                throw new \InvalidArgumentException();
            }
            $result = $this->uninstall($extension);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Based on the state of the extension, either install, upgrade or activate the extension.
     *
     * @param ExtensionEntity $extension
     * @return bool
     */
    public function enableExtension(ExtensionEntity $extension)
    {
        switch ($extension->getState()) {
            case Constant::STATE_UNINITIALISED:
                return $this->install($extension);
            case Constant::STATE_UPGRADED:
                return $this->upgrade($extension);
            case Constant::STATE_INACTIVE:
                return $this->container->get('zikula_extensions_module.extension_state_helper')->updateState($extension->getId(), Constant::STATE_ACTIVE);
            default:
                return false;
        }
    }

    /**
     * Run the console command app/console assets:install
     *
     * @throws \Exception
     */
    public function installAssets()
    {
        $kernel = $this->container->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'assets:install'
        ]);
        $output = new NullOutput();
        $application->run($input, $output);
    }

    /**
     * Get an instance of an extension Installer.
     *
     * @param AbstractBundle $bundle
     * @return ExtensionInstallerInterface
     */
    private function getExtensionInstallerInstance(AbstractBundle $bundle)
    {
        $className = $bundle->getInstallerClass();
        $reflectionInstaller = new \ReflectionClass($className);
        if (!$reflectionInstaller->isSubclassOf('\Zikula\Core\ExtensionInstallerInterface')) {
            throw new \RuntimeException($this->translator->__f("%s must implement ExtensionInstallerInterface", ['%s' => $className]));
        }
        $installer = $reflectionInstaller->newInstance();
        $installer->setBundle($bundle);
        if ($installer instanceof ContainerAwareInterface) {
            $installer->setContainer($this->container);
        }

        return $installer;
    }
}
