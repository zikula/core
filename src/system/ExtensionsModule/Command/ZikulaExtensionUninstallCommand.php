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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\ExtensionsModule\Constant;

class ZikulaExtensionUninstallCommand extends AbstractExtensionCommand
{
    protected static $defaultName = 'zikula:extension:uninstall';

    protected function configure()
    {
        $this
            ->setDescription('Uninstall a zikula module or theme')
            ->addArgument('bundle_name', InputArgument::REQUIRED, 'Bundle class name (e.g. ZikulaUsersModule)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $bundleName = $input->getArgument('bundle_name');

        if (false === $extension = $this->isInstalled($bundleName)) {
            if ($input->isInteractive()) {
                $io->error('The extension is not installed and therefore cannot be uninstalled.');
            }

            return 1;
        }

        if (Constant::STATE_MISSING === $extension->getState()) {
            if ($input->isInteractive()) {
                $io->error('The extension cannot be uninstalled because its files are missing.');
            }

            return 2;
        }

        $requiredDependents = $this->dependencyHelper->getDependentExtensions($extension);
        if (!empty($requiredDependents)) {
            if ($input->isInteractive()) {
                $names = implode(', ', array_map(function ($dependent) {
                    return $dependent->getModname();
                } , $requiredDependents));

                $io->error(sprintf('The extension is a required dependency of [%s]. Please uninstall these extensions first.', $names));
            }

            return 3;
        }

        $blocks = $this->blockRepository->findBy(['module' => $extension]);
        $this->blockRepository->remove($blocks);

        if (false === $this->extensionHelper->uninstall($extension)) {
            if ($input->isInteractive()) {
                $io->error('Could not uninstall the extension');
            }

            return 4;
        }

        $this->reSync();

        if ($input->isInteractive()) {
            $io->success('The extension has been uninstalled.');
        }

        return 0;
    }
}
