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

class ZikulaExtensionStatusCommand extends AbstractExtensionCommand
{
    protected static $defaultName = 'zikula:extension:status';

    protected function configure()
    {
        $this
            ->setDescription('Display status information of a Zikula extension')
            ->addArgument('bundle_name', InputArgument::REQUIRED, 'Bundle class name (e.g. ZikulaUsersModule)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $bundleName = $input->getArgument('bundle_name');

        if (!$input->isInteractive()) {
            $io->error('This command only runs in interactive mode.');

            return 1;
        }

        $this->reSync();
        if (null === $extension = $this->extensionRepository->findOneBy(['name' => $bundleName])) {
            $io->error('The extension cannot be found, please check the name.');
        }

        $io->title(sprintf('Status of %s', $bundleName));
        $io->table(
            ['Item', 'Value'],
            [
                ['Name', $extension->getName()],
                ['Version', $extension->getVersion()],
                ['Status', $this->translateState($extension->getState())],
                ['Description', $extension->getDescription()],
                ['Core Compatibility', $extension->getCoreCompatibility()],
        ]);

        return 0;
    }

    private function translateState(int $state): string
    {
        $translations = [
            Constant::STATE_UNINITIALISED => 'uninitialized',
            Constant::STATE_INACTIVE => 'inactive',
            Constant::STATE_ACTIVE => 'active',
            Constant::STATE_MISSING => 'missing',
            Constant::STATE_UPGRADED => 'awaiting upgrade',
            Constant::STATE_NOTALLOWED => 'not allowed',
            Constant::STATE_TRANSITIONAL => 'in process of install or uninstall',
            Constant::STATE_INVALID => 'invalid',
        ];

        if ($state > Constant::INCOMPATIBLE_CORE_SHIFT) {
            return 'Incompatible with current core.';
        }

        return $translations[$state];
    }
}
