<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class PurgeVendorsCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build:purge_vendors')
            ->setDescription('Purges tests from vendors')
            ->addUsage('my/package/path/vendor')
            ->addArgument('vendor-dir', InputArgument::REQUIRED, 'Vendors dir, e.g. src/vendor');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getArgument('vendor-dir');
        $progress = new ProgressBar($output, 4);
        $progress->start();

        self::cleanVendors($dir, $progress);
    }

    public static function cleanVendors($dir, ProgressBar $progress)
    {
        $filesystem = new Filesystem();

        $finder = new Finder();
        $finder->in($dir)
            ->directories()
            ->path('.git')
            ->path('tests')
            ->path('Tests')
            ->ignoreDotFiles(false)
            ->ignoreVCS(false);
        $progress->advance();

        $paths = [];
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $paths[] = $file->getRealPath();
        }

        $paths = array_unique($paths);
        rsort($paths);

        $progress->advance();
        $filesystem->chmod($paths, 0777, 0000, true);
        $progress->advance();
        $filesystem->remove($paths);
        $progress->advance();
    }
}
