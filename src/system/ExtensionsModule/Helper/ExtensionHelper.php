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

namespace Zikula\ExtensionsModule\Helper;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;
use Zikula\ExtensionsModule\ExtensionEvents;
use Zikula\ExtensionsModule\Installer\ExtensionInstallerInterface;

class ExtensionHelper
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    /**
     * @var ExtensionStateHelper
     */
    private $stateHelper;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        ContainerInterface $container,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionStateHelper $stateHelper,
        CacheClearer $cacheClearer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->container = $container;
        $this->translator = $translator;
        $this->variableApi = $variableApi;
        $this->extensionRepository = $extensionRepository;
        $this->stateHelper = $stateHelper;
        $this->cacheClearer = $cacheClearer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Install an extension.
     */
    public function install(ExtensionEntity $extension): bool
    {
        if (Constant::STATE_NOTALLOWED === $extension->getState()) {
            throw new RuntimeException($this->translator->trans('Error! Not allowed to install %extension%.', ['%extension%' => $extension->getName()]));
        }
        if (10 < $extension->getState()) {
            throw new RuntimeException($this->translator->trans('Error! %extension% is not compatible with this version of Zikula.', ['%extension%' => $extension->getName()]));
        }

        /** @var AbstractExtension $extensionBundle */
        $extensionBundle = $this->container->get('kernel')->getBundle($extension->getName());

        $installer = $this->getExtensionInstallerInstance($extensionBundle);
        if (null !== $installer) {
            $result = $installer->install();
            if (!$result) {
                return false;
            }
        }

        $this->stateHelper->updateState($extension->getId(), Constant::STATE_ACTIVE);
        $this->cacheClearer->clear('symfony.config');

        $event = new ExtensionStateEvent($extensionBundle, $extension->toArray());
        $this->eventDispatcher->dispatch($event, ExtensionEvents::EXTENSION_INSTALL);

        return true;
    }

    /**
     * Upgrade an extension.
     */
    public function upgrade(ExtensionEntity $extension): bool
    {
        if (Constant::STATE_NOTALLOWED === $extension->getState()) {
            throw new RuntimeException($this->translator->trans('Error! Not allowed to upgrade %extension%.', ['%extension%' => $extension->getDisplayname()]));
        }
        if (10 < $extension->getState()) {
            throw new RuntimeException($this->translator->trans('Error! %extension% is not compatible with this version of Zikula.', ['%extension%' => $extension->getDisplayname()]));
        }

        /** @var AbstractExtension $extensionBundle */
        $extensionBundle = $this->container->get('kernel')->getModule($extension->getName());

        // Check status of Dependencies here to be sure they are met for upgraded extension. #3647

        $installer = $this->getExtensionInstallerInstance($extensionBundle);
        if (null !== $installer) {
            $result = $installer->upgrade($extension->getVersion());
            if (is_string($result)) {
                if ($result !== $extension->getVersion()) {
                    // persist the last successful updated version
                    $extension->setVersion($result);
                    $this->container->get('doctrine')->getManager()->flush();
                }

                return false;
            }
            if (true !== $result) {
                return false;
            }
        }

        // persist the updated version
        $newVersion = $extensionBundle->getMetaData()->getVersion();
        $extension->setVersion($newVersion);
        $this->container->get('doctrine')->getManager()->flush();

        $this->stateHelper->updateState($extension->getId(), Constant::STATE_ACTIVE);
        $this->cacheClearer->clear('symfony');

        if ($this->container->getParameter('installed')) {
            // Upgrade succeeded, issue event.
            $event = new ExtensionStateEvent($extensionBundle, $extension->toArray());
            $this->eventDispatcher->dispatch($event, ExtensionEvents::EXTENSION_UPGRADE);
        }

        return true;
    }

    /**
     * Uninstall an extension.
     */
    public function uninstall(ExtensionEntity $extension): bool
    {
        if (Constant::STATE_NOTALLOWED === $extension->getState()
            || ZikulaKernel::isCoreExtension($extension->getName())) {
            throw new RuntimeException($this->translator->trans('Error! No permission to uninstall %extension%.', ['%extension%' => $extension->getDisplayname()]));
        }
        if (Constant::STATE_UNINITIALISED === $extension->getState()) {
            throw new RuntimeException($this->translator->trans('Error! %extension% is not yet installed, therefore it cannot be uninstalled.', ['%extension%' => $extension->getDisplayname()]));
        }

        // allow event to prevent extension removal
        $vetoEvent = new GenericEvent($extension);
        $this->eventDispatcher->dispatch($vetoEvent, ExtensionEvents::REMOVE_VETO);
        if ($vetoEvent->isPropagationStopped()) {
            return false;
        }

        /** @var \Zikula\ExtensionsModule\AbstractExtension $extensionBundle */
        $extensionBundle = $this->container->get('kernel')->getBundle($extension->getName());

        $installer = $this->getExtensionInstallerInstance($extensionBundle);
        if (null !== $installer) {
            $result = $installer->uninstall();
            if (!$result) {
                return false;
            }
        }

        // remove remaining extension variables
        $this->variableApi->delAll($extension->getName());

        // remove the entry from the modules table
        $this->extensionRepository->removeAndFlush($extension);

        $this->cacheClearer->clear('symfony.config');

        $event = new ExtensionStateEvent($extensionBundle, $extension->toArray());
        $this->eventDispatcher->dispatch($event, ExtensionEvents::EXTENSION_REMOVE);

        return true;
    }

    /**
     * Uninstall an array of extensions.
     *
     * @param ExtensionEntity[] $extensions
     */
    public function uninstallArray(array $extensions): bool
    {
        foreach ($extensions as $extension) {
            if (!$extension instanceof ExtensionEntity) {
                throw new InvalidArgumentException();
            }
            $result = $this->uninstall($extension);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Run the console command assets:install.
     *
     * @throws Exception
     */
    public function installAssets(): bool
    {
        /** @var ZikulaHttpKernelInterface $kernel */
        $kernel = $this->container->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'assets:install'
        ]);
        $output = new NullOutput();
        $application->run($input, $output);

        return true;
    }

    /**
     * Attempt to get an instance of an extension Installer.
     */
    private function getExtensionInstallerInstance(AbstractExtension $extension): ?ExtensionInstallerInterface
    {
        $className = $extension->getInstallerClass();
        if (!class_exists($className)) {
            return null;
        }
        $reflectionInstaller = new ReflectionClass($className);
        if (!$reflectionInstaller->isSubclassOf(ExtensionInstallerInterface::class)) {
            throw new RuntimeException($this->translator->trans('%extension% must implement ExtensionInstallerInterface', ['%extension%' => $className]));
        }
        /** @var ExtensionInstallerInterface $installer */
        $installer = $reflectionInstaller->newInstance();
        $installer->setExtension($extension);
        if ($installer instanceof ContainerAwareInterface) {
            $installer->setContainer($this->container);
        }

        return $installer;
    }
}
