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

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;
use Zikula\ExtensionsModule\ExtensionEvents;

class ZikulaExtensionInstallCommand extends AbstractExtensionCommand
{
    protected static $defaultName = 'zikula:extension:install';

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

        if (false !== $this->isInstalled($bundleName)) {
            if ($input->isInteractive()) {
                $io->error('Extension is already installed but possibly inactive.');
            }

            return 1;
        }

        /** @var $extension ExtensionEntity */
        if (false === $extension = $this->load($bundleName)) {
            if ($input->isInteractive()) {
                $io->error('Extension could not be found and loaded from the expected directory');
            }

            return 2;
        }

        if (!$ignoreDeps) {
            if (false === $this->installDependencies($extension)) {
                if ($input->isInteractive()) {
                    $io->error('Required dependencies could not be installed');
                }

                return 3;
            }
        }

        if (false === $this->extensionHelper->install($extension)) {
            if ($input->isInteractive()) {
                $io->error('Could not install the extension');
            }

            return 4;
        }

        if (0 !== $this->clearCache()) {
            if ($input->isInteractive()) {
                $io->error('Could not clear the cache (--no-warmup)');
            }

            return 5;
        }

        $event = new ExtensionStateEvent($this->kernel->getModule($extension->getName()), $extension->toArray());
        $this->eventDispatcher->dispatch($event, ExtensionEvents::EXTENSION_POSTINSTALL);

        if ($input->isInteractive()) {
            $io->success('Extension installed');
        }

        return 0;
    }

    private function clearCache(): int
    {
        $command = $this->getApplication()->find('cache:clear');

        return $command->run(new ArrayInput(['--no-warmup' => true]), new NullOutput());
    }

    private function load(string $bundleName)
    {
        // load the extension into the modules table
        $this->reSync(false);
        if (!($extension = $this->extensionRepository->findOneBy(['name' => $bundleName]))) {
            return false;
        }

        // force the kernel to load the bundle
        $extension->setState(Constant::STATE_TRANSITIONAL);
        $this->extensionRepository->persistAndFlush($extension);
        $this->kernel->reboot(null);

        return $extension;
    }

    private function installDependencies(ExtensionEntity $extension): bool
    {
        $unsatisfiedDependencies = $this->dependencyHelper->getUnsatisfiedExtensionDependencies($extension);
        $return = true;
        foreach ($unsatisfiedDependencies as $dependency) {
            if (MetaData::DEPENDENCY_REQUIRED !== $dependency->getStatus()) {
                continue;
            }
            $this->load($dependency->getModname());
            $return = $return && $this->extensionHelper->install($dependency);
        }

        return $return;
    }
}
