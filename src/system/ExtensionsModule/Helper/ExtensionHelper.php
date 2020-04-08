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

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Collector\InstallerCollector;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Event\ExtensionEntityPreRemoveEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostInstallEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostRemoveEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostUpgradeEvent;
use Zikula\ExtensionsModule\Installer\ExtensionInstallerInterface;

class ExtensionHelper
{
    /**
     * @var string
     */
    private $installed;

    /**
     * @var InstallerCollector
     */
    private $installerCollector;

    /**
     * @var KernelInterface
     */
    private $kernel;

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

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    public function __construct(
        $installed,
        InstallerCollector $installerCollector,
        KernelInterface $kernel,
        ManagerRegistry $managerRegistry,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi,
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionStateHelper $stateHelper,
        CacheClearer $cacheClearer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->installed = $installed;
        $this->installerCollector = $installerCollector;
        $this->kernel = $kernel;
        $this->doctrine = $managerRegistry;
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
        $extensionBundle = $this->kernel->getBundle($extension->getName());

        $installer = $this->getExtensionInstallerInstance($extensionBundle);
        if (null !== $installer) {
            $result = $installer->install();
            if (!$result) {
                return false;
            }
        }

        $this->stateHelper->updateState($extension->getId(), Constant::STATE_ACTIVE);
        $this->cacheClearer->clear('symfony.config');

        $this->eventDispatcher->dispatch(new ExtensionPostInstallEvent($extensionBundle, $extension));

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
        $extensionBundle = $this->kernel->getBundle($extension->getName());

        // Check status of Dependencies here to be sure they are met for upgraded extension. #3647

        $installer = $this->getExtensionInstallerInstance($extensionBundle);
        if (null !== $installer) {
            $result = $installer->upgrade($extension->getVersion());
            if (is_string($result)) {
                if ($result !== $extension->getVersion()) {
                    // persist the last successful updated version
                    $extension->setVersion($result);
                    $this->doctrine->getManager()->flush();
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
        $this->doctrine->getManager()->flush();

        $this->stateHelper->updateState($extension->getId(), Constant::STATE_ACTIVE);
        $this->cacheClearer->clear('symfony');

        if ($this->installed) {
            // Upgrade succeeded, issue event.
            $this->eventDispatcher->dispatch(new ExtensionPostUpgradeEvent($extensionBundle, $extension));
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
        $extensionEntityPreRemoveEvent = new ExtensionEntityPreRemoveEvent($extension);
        $this->eventDispatcher->dispatch($extensionEntityPreRemoveEvent);
        if ($extensionEntityPreRemoveEvent->isPropagationStopped()) {
            return false;
        }

        /** @var \Zikula\ExtensionsModule\AbstractExtension $extensionBundle */
        $extensionBundle = $this->kernel->getBundle($extension->getName());

        $installer = $this->getExtensionInstallerInstance($extensionBundle);
        if (null !== $installer) {
            $result = $installer->uninstall();
            if (!$result) {
                return false;
            }
        }

        // remove remaining extension variables
        $this->variableApi->delAll($extension->getName());

        // remove the entry from the extensions table
        $this->extensionRepository->removeAndFlush($extension);

        $this->cacheClearer->clear('symfony.config');

        $this->eventDispatcher->dispatch(new ExtensionPostRemoveEvent($extensionBundle, $extension));

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
        $kernel = $this->kernel;
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
     * Attempt to get an extension Installer.
     */
    private function getExtensionInstallerInstance(AbstractExtension $extension): ?ExtensionInstallerInterface
    {
        $className = $extension->getInstallerClass();
        if (!class_exists($className)) {
            return null;
        }
        if ($this->installerCollector->has($className)) {
            return $this->installerCollector->get($className);
        }

        return null;
    }
}
