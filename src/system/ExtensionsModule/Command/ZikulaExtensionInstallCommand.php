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

namespace Zikula\ExtensionsModule\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\Bundle\MetaData;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\CoreEvents;
use Zikula\Core\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;
use Zikula\ExtensionsModule\Helper\ExtensionDependencyHelper;
use Zikula\ExtensionsModule\Helper\ExtensionHelper;

class ZikulaExtensionInstallCommand extends Command
{
    protected static $defaultName = 'zikula:extension:install';

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    /**
     * @var ExtensionDependencyHelper
     */
    private $dependencyHelper;

    /**
     * @var BundleSyncHelper
     */
    private $bundleSyncHelper;

    /**
     * @var ExtensionHelper
     */
    private $extensionHelper;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(
        ExtensionRepositoryInterface $extensionRepository,
        ExtensionDependencyHelper $dependencyHelper,
        BundleSyncHelper $bundleSyncHelper,
        ExtensionHelper $extensionHelper,
        EventDispatcherInterface $eventDispatcher,
        ZikulaHttpKernelInterface $kernel
    ) {
        $this->extensionRepository = $extensionRepository;
        $this->dependencyHelper = $dependencyHelper;
        $this->bundleSyncHelper = $bundleSyncHelper;
        $this->extensionHelper = $extensionHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->kernel = $kernel;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Install a zikula extension (module or theme).')
            ->addArgument('bundle_name', InputArgument::REQUIRED, 'Bundle class name (e.g. ZikulaUsersModule)')
            ->addOption(
                'ignore_deps',
                null,
                InputOption::VALUE_NONE,
                'Force install the extension ignoring all dependencies')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $bundleName = $input->getArgument('bundle_name');
        $ignoreDeps = $input->getOption('ignore_deps');

        if (false === $extension = $this->load($bundleName, $output)) {
            if ($input->isInteractive()) {
                $io->error('Extension could not be found and loaded from the expected directory');
            }

            return 1;
        }

        if (!$ignoreDeps) {
            if (false === $this->installDependencies($extension)) {
                if ($input->isInteractive()) {
                    $io->error('Required dependencies could not be installed');
                }

                return 2;
            }
        }

        if (false === $this->install($extension)) {
            if ($input->isInteractive()) {
                $io->error('Could not install the extension');
            }

            return 3;
        }

        if (0 !== $this->clearCache($output)) {
            if ($input->isInteractive()) {
                $io->error('Could not clear the cache');
            }

            return 4;
        }

        $event = new ModuleStateEvent($this->kernel->getModule($extension->getName()), $extension->toArray());
        $this->eventDispatcher->dispatch($event, CoreEvents::MODULE_POSTINSTALL);

        if ($input->isInteractive()) {
            $io->success('Extension installed');
        }

        return 0;
    }

    private function clearCache(OutputInterface $output): int
    {
        $command = $this->getApplication()->find('cache:clear');
        return $command->run(new ArrayInput(['--quiet', '--no-debug']), $output);
    }

    private function load($bundleName, OutputInterface $output)
    {
        // load the extension into the modules table
        $this->bundleSyncHelper->scanForBundles();
        if (!($extension = $this->extensionRepository->findOneBy(['name' => $bundleName]))) {

            return false;
        }

        // force the kernel to load the bundle
        $extension->setState(Constant::STATE_TRANSITIONAL);
        $this->extensionRepository->persistAndFlush($extension);
        if (0 !== $this->clearCache($output)) {
            return false;
        }

        return $extension;
    }

    private function installDependencies(ExtensionEntity $extension)
    {
        $unsatisfiedDependencies = $this->dependencyHelper->getUnsatisfiedExtensionDependencies($extension);
        foreach ($unsatisfiedDependencies as $dependency) {
            if (MetaData::DEPENDENCY_REQUIRED !== $dependency->getStatus()) {
                continue;
            }
            $this->install($dependency);
        }
    }

    private function install(ExtensionEntity $extension)
    {
        $this->extensionHelper->install($extension);
    }
}
