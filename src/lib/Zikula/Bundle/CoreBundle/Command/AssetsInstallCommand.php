<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Command that places bundle web assets into a given directory.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class AssetsInstallCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('assets:install')
            ->setDefinition([
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory', 'web'),
            ])
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
            ->setDescription('Installs web assets under a public web directory')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command installs bundle assets into a given
directory (e.g. the web directory).

<info>php %command.full_name% web</info>

A "modules" and "themes" directory will be created inside the target directory, and the
"Resources/public" directory of each bundle will be copied into it.

To create a symlink to each bundle instead of copying its assets, use the
<info>--symlink</info> option:

<info>php %command.full_name% web --symlink</info>

To make symlink relative, add the <info>--relative</info> option:

<info>php %command.full_name% web --symlink --relative</info>

EOT
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetArg = rtrim($input->getArgument('target'), '/');
        if (!is_dir($targetArg)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $input->getArgument('target')));
        }
        if (!function_exists('symlink') && $input->getOption('symlink')) {
            throw new \InvalidArgumentException('The symlink() function is not available on your system. You need to install the assets without the --symlink option.');
        }
        $filesystem = $this->getContainer()->get('filesystem');
        $kernel = $this->getContainer()->get('kernel');
        $array = [
            'bundle' => $kernel->getJustBundles(),
            'module' => $kernel->getModules(),
            'theme' => $kernel->getThemes(),
        ];
        foreach ($array as $type => $bundles) {
            // Create the bundles directory otherwise symlink will fail.
            $filesystem->mkdir($targetArg . "/{$type}s/", 0777);
            $output->writeln(sprintf("Installing assets using the <comment>%s</comment> option", $input->getOption('symlink') ? 'symlink' : 'hard copy'));
            foreach ($bundles as $bundle) {
                if (is_dir($originDir = $bundle->getPath() . '/Resources/public')) {
                    $bundlesDir = $targetArg . '/' . $type . 's/';
                    $targetDir = $bundlesDir . preg_replace('/' . $type . '$/', '', strtolower($bundle->getName()));
                    $output->writeln(sprintf('Installing assets for <comment>%s</comment> into <comment>%s</comment>', $bundle->getNamespace(), $targetDir));
                    $filesystem->remove($targetDir);
                    if ($input->getOption('symlink')) {
                        if ($input->getOption('relative')) {
                            $relativeOriginDir = $filesystem->makePathRelative($originDir, realpath($bundlesDir));
                        } else {
                            $relativeOriginDir = $originDir;
                        }
                        $filesystem->symlink($relativeOriginDir, $targetDir);
                    } else {
                        $filesystem->mkdir($targetDir, 0777);
                        // We use a custom iterator to ignore VCS files
                        $filesystem->mirror($originDir, $targetDir, Finder::create()->in($originDir));
                    }
                }
            }
        }
    }
}
