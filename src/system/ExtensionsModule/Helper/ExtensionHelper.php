<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Helper;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\AbstractBundle;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\Core\ExtensionInstallerInterface;
use Zikula\ExtensionsModule\Api\ExtensionApi;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Helper\Legacy\ExtensionHelper as LegacyExtensionHelper;

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
     * @var ExtensionApi
     */
    private $extensionApi;

    /**
     * ExtensionHelper constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $container->get('translator.default');
        $this->translator->setLocale('ZikulaExtensionsModule');
        $this->extensionApi = $container->get('zikula_extensions_module.api.extension');
    }

    public function upgrade(ExtensionEntity $extension)
    {
        switch ($extension->getState()) {
            case ExtensionApi::STATE_NOTALLOWED:
                throw new \RuntimeException($this->translator->__f('Error! Not allowed to upgrade %s.', ['%s' => $extension->getDisplayname()]));
                break;
            default:
                if ($extension->getState() > 10) {
                    throw new \RuntimeException($this->translator->__f('Error! %s is not compatible with this version of Zikula.', ['%s' => $extension->getDisplayname()]));
                }
        }

        if ($extension->getType() == self::TYPE_SYSTEM) {
            // system modules are always loaded
            $bundle = $this->container->get('kernel')->getModule($extension->getName());
        } else {
            $bundle = $this->forceLoadExtension($extension);
        }
        if (null === $bundle) {
            return LegacyExtensionHelper::upgrade($extension);
        }

        $installer = $this->getExtensionInstallerInstance($bundle);
        $result = $installer->upgrade($extension->getVersion());
        if (is_string($result)) {
            if ($result != $extension->getVersion()) {
                // persist the last successful updated version
                $extension->setVersion($result);
                $this->container->get('doctrine.entitymanager')->flush();
            }

            return false;
        } elseif (true !== $result) {
            return false;
        }
        // persist the updated version
        $newVersion = $bundle->getMetaData()->getVersion();
        $extension->setVersion($newVersion);
        $this->container->get('doctrine.entitymanager')->flush();

        $this->container->get('zikula_extensions_module.extension_state_helper')->updateState($extension->getId(), ExtensionApi::STATE_ACTIVE);

        $this->container->get('zikula.cache_clearer')->clear('symfony');

        if (!\System::isInstalling()) {
            // Upgrade succeeded, issue event.
            $event = new ModuleStateEvent($bundle, null);
            $this->container->get('event_dispatcher')->dispatch(CoreEvents::MODULE_UPGRADE, $event);
        }

        return true;
    }

    /**
     * Get an instance of a bundle class that is not currently loaded into the kernel.
     * Note: All System modules are always loaded into the kernel.
     *
     * @param ExtensionEntity $extension
     * @return null|AbstractBundle
     */
    private function forceLoadExtension(ExtensionEntity $extension)
    {
        $osDir = \DataUtil::formatForOS($extension->getDirectory());
        $scanner = new Scanner();
        $scanner->scan(["modules/$osDir"], 1);
        $modules = $scanner->getModulesMetaData(true);
        /** @var $moduleMetaData \Zikula\Bundle\CoreBundle\Bundle\MetaData */
        $moduleMetaData = !empty($modules[$extension->getName()]) ? $modules[$extension->getName()] : null;
        if (null !== $moduleMetaData) {
            // moduleMetaData only exists for bundle-type modules
            $boot = new \Zikula\Bundle\CoreBundle\Bundle\Bootstrap();
            $boot->addAutoloaders($this->container->get('kernel'), $moduleMetaData->getAutoload());
            if ($extension->getType() == self::TYPE_MODULE) {
                if (is_dir("modules/$osDir/Resources/locale")) {
                    \ZLanguage::bindModuleDomain($extension->getName());
                }
            }
            $moduleClass = $moduleMetaData->getClass();

            $bundle = new $moduleClass();
            $bootstrap = $bundle->getPath() . "/bootstrap.php";
            if (file_exists($bootstrap)) {
                include_once $bootstrap;
            }

            return $bundle;
        }

        return null;
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
            throw new \RuntimeException($this->translator->__f("%s must implement ExtensionInstallerInterface", $className));
        }
        $installer = $reflectionInstaller->newInstance();
        $installer->setBundle($bundle);
        if ($installer instanceof ContainerAwareInterface) {
            $installer->setContainer($this->container);
        }

        return $installer;
    }
}
