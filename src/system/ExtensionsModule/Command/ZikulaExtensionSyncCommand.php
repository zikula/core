<?php

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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;

class ZikulaExtensionSyncCommand extends Command
{
    /**
     * @var BundleSyncHelper
     */
    private $bundleSyncHelper;

    protected static $defaultName = 'zikula:extension:sync';

    public function __construct(BundleSyncHelper $bundleSyncHelper)
    {
        $this->bundleSyncHelper = $bundleSyncHelper;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Sync bundles in a directory with the bundles table and the extensions table.')
            ->addArgument('directory', InputArgument::OPTIONAL, 'Directory to scan e.g. src/system. Optional: (default \'src/extensions\')')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = null !== $input->getArgument('directory') ? $input->getArgument('directory') : 'src/extensions';
        if (!is_dir($directory)) {
            $io->error('Directory is invalid (`' . $directory . '`)');

            return 1;
        }

        $extensionsInFileSystem = $this->bundleSyncHelper->scanForBundles([$directory]);
        if (empty($extensionsInFileSystem)) {
            $io->warning('There were no extensions found in ' . $directory);
        } else {
            $io->title('Extensions in directory');
            $io->listing(array_keys($extensionsInFileSystem));
        }

        $upgraded = $this->bundleSyncHelper->syncExtensions($extensionsInFileSystem);
        if (empty($upgraded)) {
            $io->warning('There were no upgraded extensions found in ' . $directory);
        } else {
            $io->title('Upgraded extensions');
            $io->listing(array_keys($upgraded));
        }

        $io->success('Complete');

        return 0;
    }
}
