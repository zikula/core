#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

require __DIR__.'/src/vendor/autoload.php';

class ControllerActionCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('module:controller_actions')
            ->setDescription('Adds "Action" suffix to all public controller methods in specified controller directory')
            ->addOption('dir', null, InputOption::VALUE_REQUIRED,
                'Target directory is mandatory - should be the Controller folder of a module'
            )
        ->setHelp(<<<EOF
The <info>module:controller_actions</info> command refactors controller methods with Action suffix.

<info>refactor module:controller_actions --dir=modules/MyModule/Controller</info>
EOF
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getOption('dir');
        if (!$dir) {
            $output->writeln("<error>ERROR: --dir= is required</error>");
            exit(1);
        }

        if (!is_dir($dir)) {
            $output->writeln("<error>$dir does not exist</error>");
            exit(1);
        }

        $finder = new Finder();
        $finder->in($dir)
            ->files()
            ->depth(0)
            ->name('*.php');
        foreach ($finder as $file) {
            echo 'Processing '.$file->getRealPath()."\n";
            $content = file_get_contents($file->getRealPath());
            $content = preg_replace('/public function (\w+)\(/', 'public function $1Action(', $content);
            file_put_contents($file->getRealPath(), $content);
        };

        $output->writeln("<comment>Done.

Remember to update Version.php core_min to 1.3.6</comment>");
    }
}

class MigrateResourceStructure extends Command
{
    protected function configure()
    {
        $this
            ->setName('module:restructure')
            ->setDescription('Creates and moves structure')
            ->addOption('dir', null, InputOption::VALUE_REQUIRED,
                'Target directory is mandatory - should be module directory'
            )
            ->addOption('module', null, InputOption::VALUE_REQUIRED,
                'Module name mandatory - should be module directory name'
            )
        ->setHelp(<<<EOF
The <info>module:restructure</info> command migrates resources</info>

<info>refactor module:restructure --dir=modules/MyModule --module=MyModule</info>
EOF
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getOption('dir');
        if (!$dir) {
            $output->writeln("<error>ERROR: --dir= is required</error>");
            exit(1);
        }
        $moduleDir = $input->getOption('module');
        if (!$moduleDir) {
            $output->writeln("<error>ERROR: --module= is required</error>");
            exit(1);
        }

        if (!is_dir($dir)) {
            $output->writeln("<error>ERROR: $dir does not exist</error>");
            exit(1);
        }

        if (!is_dir($dir.'/Resources/public')) {
            if (mkdir($dir.'/Resources/public', 0755, true)) {
                $output->writeln("<info>Created $dir/Resources/public</info>");
            } else {
                $output->writeln("<error>Failed to create $dir/Resources/public</error>");
                return;
            }
        }

        if (is_dir($dir.'/style')) {
            `git mv $dir/style $dir/Resources/public/css`;
             $output->writeln("<info>moved $dir/style to $dir/Resources/public/css</info>");
        }

        if (is_dir($dir.'/javascript')) {
            `git mv $dir/javascript $dir/Resources/public/js`;
            $output->writeln("<info>moved $dir/javascript to $dir/Resources/public/js</info>");
        }

        if (is_dir($dir.'/images')) {
            `git mv $dir/images $dir/Resources/public`;
            $output->writeln("<info>moved $dir/images to $dir/Resources/public/images</info>");
        }

        if (is_dir($dir.'/docs')) {
            `git mv $dir/docs $dir/Resources/docs`;
            $output->writeln("<info>moved $dir/docs to $dir/Resources/public/docs</info>");
        }

        if (is_dir($dir.'/locale')) {
            `git mv $dir/locale $dir/Resources`;
            $output->writeln("<info>moved $dir/locale to $dir/Resources/public/locale</info>");
        }

        if (is_dir($dir.'/templates')) {
            `git mv $dir/templates $dir/Resources/views`;
            $output->writeln("<info>moved $dir/templates to $dir/Resources/public/views</info>");
        }

        if (is_dir($dir.'/lib/'.$moduleDir)) {
            `mv $dir/lib/$moduleDir/* $dir`;
            `git add $dir/*`;
            rmdir("$dir/lib/$moduleDir");
            @rmdir("$dir/lib"); // there might be a vendor dir here so suppress warnings
            if (is_dir("$dir/lib")) {
                $output->writeln("<comment>Please relocate any vendors from $dir/lib/vendor into $dir/vendor/</comment>");
            }
            $output->writeln("<info>moved PHP files from $dir/lib/$moduleDir/* to $dir</info>");
        }

        $output->writeln("<info>Done.
Todo tasks:

  - Update Version.php core_min to 1.3.6
  - If there are any old calls to {pageaddvar} specifying js/css paths, these must be tweaked
</info>");
        $output->writeln("<comment>To finish please run:\n\n    git commit -a -m Restructured</comment>");
    }
}

$application = new Application();
$application->add(new ControllerActionCommand());
$application->add(new MigrateResourceStructure());
$application->run();
