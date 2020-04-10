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
use Zikula\ExtensionsModule\Constant;

class ZikulaExtensionUpgradeCommand extends AbstractExtensionCommand
{
    protected static $defaultName = 'zikula:extension:upgrade';

    protected function configure()
    {
        $this
            ->setDescription('Upgrade a zikula module or theme')
            ->addArgument('bundle_name', InputArgument::REQUIRED, 'Bundle class name (e.g. ZikulaUsersModule)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $bundleName = $input->getArgument('bundle_name');

        if (false === $this->isInstalled($bundleName)) {
            if ($input->isInteractive()) {
                $io->error('The extension is not installed and therefore cannot be upgraded.');
            }

            return 1;
        }

        if (false !== $extension = $this->isUpgradeable($bundleName)) {
            if ($input->isInteractive()) {
                $io->error('The extension cannot be upgraded because its version number has not changed.');
            }

            return 2;
        }

        if (!$this->extensionHelper->upgrade($extension)) {
            if ($input->isInteractive()) {
                $io->error('The extension could not be upgraded.');
            }

            return 3;
        }

        if ($input->isInteractive()) {
            $io->success('The extension has been upgraded.');
        }

        return 0;
    }

    private function isUpgradeable(string $bundleName)
    {
        $this->reSync(false);
        if (null !== $extension = $this->extensionRepository->findOneBy(['name' => $bundleName])) {
            if (Constant::STATE_UPGRADED === $extension->getState()) {
                return $extension;
            }
        }

        return false;
    }
}
