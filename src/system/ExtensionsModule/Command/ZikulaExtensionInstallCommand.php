<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Event\ExtensionPostCacheRebuildEvent;

class ZikulaExtensionInstallCommand extends AbstractExtensionCommand
{
    protected static $defaultName = 'zikula:extension:install';

    protected function configure()
    {
        $this
            ->setDescription('Install a zikula extension (module or theme). You must run this command twice.')
            ->addArgument('bundle_name', InputArgument::REQUIRED, 'Bundle class name (e.g. ZikulaUsersModule)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $bundleName = $input->getArgument('bundle_name');

        if (false !== $this->isInstalled($bundleName)) {
            if ($input->isInteractive()) {
                $io->error('Extension is already installed but possibly inactive.');
            }

            return 1;
        }

        if (!$this->kernel->isBundle($bundleName)) {
            $this->load($bundleName);
            $io->note(sprintf('%s is now prepared for installation. Run this command again to complete installation.', $bundleName));

            return 0;
        }

        /** @var $extension ExtensionEntity */
        $extension = $this->extensionRepository->findOneBy(['name' => $bundleName]);
        $unsatisfiedDependencies = $this->dependencyHelper->getUnsatisfiedExtensionDependencies($extension);
        $dependencyNames = [];
        foreach ($unsatisfiedDependencies as $dependency) {
            if (MetaData::DEPENDENCY_REQUIRED !== $dependency->getStatus()) {
                continue;
            }
            $dependencyNames[] = $dependency->getModname();
        }
        if (!empty($dependencyNames)) {
            $io->error(sprintf('Cannot install because this extension depends on other extensions. Please install the following extensions first: %s', implode(', ', $dependencyNames)));

            return 2;
        }

        if (false === $this->extensionHelper->install($extension)) {
            if ($input->isInteractive()) {
                $io->error('Could not install the extension');
            }

            return 3;
        }

        $this->eventDispatcher->dispatch(new ExtensionPostCacheRebuildEvent($this->kernel->getBundle($extension->getName()), $extension));

        if ($input->isInteractive()) {
            $io->success('Extension installed');
        }

        return 0;
    }


    private function load(string $bundleName): void
    {
        // load the extension into the modules table
        $this->reSync(false);
        if (!($extension = $this->extensionRepository->findOneBy(['name' => $bundleName]))) {
            throw new \Exception(sprintf('Could not find extension %s in the database', $bundleName));
        }

        // force the kernel to load the bundle
        $extension->setState(Constant::STATE_TRANSITIONAL);
        $this->extensionRepository->persistAndFlush($extension);

        // delete the container file
        $cacheDir = $this->kernel->getContainer()->getParameter('kernel.cache_dir');
        $containerClass = $this->kernel->getContainer()->getParameter('kernel.container_class');
        $containerFile = $cacheDir . '/' . $containerClass . '.php';
        $fs = new Filesystem();
        $fs->remove($containerFile);
        $this->kernel->getContainer()->get('cache_warmer')->warmUp($this->kernel->getCacheDir());
        $this->kernel->reboot(null);
    }
}
