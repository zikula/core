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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Console\Application;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\AbstractBundle;
use Zikula\Core\AbstractModule;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\Core\ExtensionInstallerInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\ExtensionEvents;

class ExtensionHelper
{
    public const TYPE_SYSTEM = 3;

    public const TYPE_MODULE = 2;

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
            throw new RuntimeException($this->translator->trans('Error! Not allowed to install %s.', ['%s' => $extension->getName()]));
        }
        if (10 < $extension->getState()) {
            throw new RuntimeException($this->translator->trans('Error! %s is not compatible with this version of Zikula.', ['%s' => $extension->getName()]));
        }

        $bundle = $this->container->get('kernel')->getBundle($extension->getName());

        $installer = $this->getExtensionInstallerInstance($bundle);
        $result = $installer->install();
        if (!$result) {
            return false;
        }

        $this->stateHelper->updateState($extension->getId(), Constant::STATE_ACTIVE);
        $this->cacheClearer->clear('symfony.config');

        $event = new ModuleStateEvent($bundle, $extension->toArray());
        $this->eventDispatcher->dispatch($event, CoreEvents::MODULE_INSTALL);

        return true;
    }

    /**
     * Upgrade an extension.
     */
    public function upgrade(ExtensionEntity $extension): bool
    {
        if (Constant::STATE_NOTALLOWED === $extension->getState()) {
            throw new RuntimeException($this->translator->trans('Error! Not allowed to upgrade %s.', ['%s' => $extension->getDisplayname()]));
        }
        if (10 < $extension->getState()) {
            throw new RuntimeException($this->translator->trans('Error! %s is not compatible with this version of Zikula.', ['%s' => $extension->getDisplayname()]));
        }

        /** @var AbstractModule $bundle */
        $bundle = $this->container->get('kernel')->getModule($extension->getName());

        // Check status of Dependencies here to be sure they are met for upgraded extension. #3647

        $installer = $this->getExtensionInstallerInstance($bundle);
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

        // persist the updated version
        $newVersion = $bundle->getMetaData()->getVersion();
        $extension->setVersion($newVersion);
        $this->container->get('doctrine')->getManager()->flush();

        $this->stateHelper->updateState($extension->getId(), Constant::STATE_ACTIVE);
        $this->cacheClearer->clear('symfony');

        if ($this->container->getParameter('installed')) {
            // Upgrade succeeded, issue event.
            $event = new ModuleStateEvent($bundle, $extension->toArray());
            $this->eventDispatcher->dispatch($event, CoreEvents::MODULE_UPGRADE);
        }

        return true;
    }

    /**
     * Uninstall an extension.
     */
    public function uninstall(ExtensionEntity $extension): bool
    {
        if (Constant::STATE_NOTALLOWED === $extension->getState()
            || ZikulaKernel::isCoreModule($extension->getName())) {
            throw new RuntimeException($this->translator->trans('Error! No permission to uninstall %s.', ['%s' => $extension->getDisplayname()]));
        }
        if (Constant::STATE_UNINITIALISED === $extension->getState()) {
            throw new RuntimeException($this->translator->trans('Error! %s is not yet installed, therefore it cannot be uninstalled.', ['%s' => $extension->getDisplayname()]));
        }

        // allow event to prevent extension removal
        $vetoEvent = new GenericEvent($extension);
        $this->eventDispatcher->dispatch($vetoEvent, ExtensionEvents::REMOVE_VETO);
        if ($vetoEvent->isPropagationStopped()) {
            return false;
        }

        $bundle = $this->container->get('kernel')->getBundle($extension->getName());

        $installer = $this->getExtensionInstallerInstance($bundle);
        $result = $installer->uninstall();
        if (!$result) {
            return false;
        }

        // remove remaining extension variables
        $this->variableApi->delAll($extension->getName());

        // remove the entry from the modules table
        $this->extensionRepository->removeAndFlush($extension);

        $this->cacheClearer->clear('symfony.config');

        $event = new ModuleStateEvent($bundle, $extension->toArray());
        $this->eventDispatcher->dispatch($event, CoreEvents::MODULE_REMOVE);

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
     * Based on the state of the extension, either install, upgrade or activate the extension.
     */
    public function enableExtension(ExtensionEntity $extension): bool
    {
        switch ($extension->getState()) {
            case Constant::STATE_UNINITIALISED:
                return $this->install($extension);
            case Constant::STATE_UPGRADED:
                return $this->upgrade($extension);
            case Constant::STATE_INACTIVE:
                return $this->stateHelper->updateState($extension->getId(), Constant::STATE_ACTIVE);
            default:
                return false;
        }
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
     * Get an instance of an extension Installer.
     */
    private function getExtensionInstallerInstance(AbstractBundle $bundle): ExtensionInstallerInterface
    {
        $className = $bundle->getInstallerClass();
        $reflectionInstaller = new ReflectionClass($className);
        if (!$reflectionInstaller->isSubclassOf(ExtensionInstallerInterface::class)) {
            throw new RuntimeException($this->translator->trans('%s must implement ExtensionInstallerInterface', ['%s' => $className]));
        }
        /** @var ExtensionInstallerInterface $installer */
        $installer = $reflectionInstaller->newInstance();
        $installer->setBundle($bundle);
        if ($installer instanceof ContainerAwareInterface) {
            $installer->setContainer($this->container);
        }

        return $installer;
    }
}
