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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;

class ZikulaExtensionSyncCommand extends Command
{
    protected static $defaultName = 'zikula:extension:sync';

    /**
     * @var BundleSyncHelper
     */
    private $bundleSyncHelper;

    public function __construct(BundleSyncHelper $bundleSyncHelper)
    {
        parent::__construct();
        $this->bundleSyncHelper = $bundleSyncHelper;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sync bundles in a directory with the bundles table and the extensions table.')
            ->addOption(
                'include_core',
                null,
                InputOption::VALUE_NONE,
                'Include the core extensions'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $includeCore = $input->getOption('include_core') ?? false;

        $extensionsInFileSystem = $this->bundleSyncHelper->scanForBundles($includeCore);
        if (empty($extensionsInFileSystem)) {
            $io->warning('There were no extensions found in src/extensions.');
        } else {
            $io->title('Extensions in directory');
            $io->listing(array_keys($extensionsInFileSystem));
        }

        $upgraded = $this->bundleSyncHelper->syncExtensions($extensionsInFileSystem);
        if (empty($upgraded)) {
            $io->warning('There were no upgraded extensions found in src/extensions.');
        } else {
            $io->title('Upgraded extensions');
            $io->listing(array_keys($upgraded));
        }

        $io->success('Complete');

        return 0;
    }
}
