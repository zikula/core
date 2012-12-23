#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

include 'src/lib/ZLoader.php';
ZLoader::register();

class ControllerActionCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('zk:action_controller')
            ->setDescription('Adds Action suffic to all public controller methods in specified controller directory')
            ->addOption('dir', null, InputOption::VALUE_REQUIRED,
                'Target directory is mandatory - should be the Controller folder of a module'
            )
        ->setHelp(<<<EOF
The <info>zk:action_controller</info> command refactors controller methods with Action suffix.

<info>refactor zk:action_controller --dir=modules/MyModule/Controller</info>
EOF
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getOption('dir');
        if (!$dir) {
            $output->writeln("--dir= is required\n");
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

        $output->writeln("done\n");
    }
}

class MigrateResourceStructure extends Command
{
    protected function configure()
    {
        $this
            ->setName('zk:migrate_resource')
            ->setDescription('Creates and moves structure')
            ->addOption('dir', null, InputOption::VALUE_REQUIRED,
                'Target directory is mandatory - should be module directory'
            )
        ->setHelp(<<<EOF
The <info>zk:migrate_resource</info> command migrates resources</info>

<info>refactor zk:migrate_resource --dir=modules/MyModule</info>
EOF
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getOption('dir');
        if (!$dir) {
            $output->writeln("--dir= is required\n");
        }

        if (!is_dir($dir.'/Resources/public')) {
            if (mkdir($dir.'/Resources/public', 0755, true)) {
                $output->writeln("Created $dir/Resources/public\n");
            } else {
                $output->writeln("Failed to create $dir/Resources/public\n");
                return;
            }
        }

        if (is_dir($dir.'/style')) {
            `git mv $dir/style $dir/Resources/public/css`;
             $output->writeln("moved $dir/style to $dir/Resources/public/css\n");
        }

        if (is_dir($dir.'/javascript')) {
            `git mv $dir/javascript $dir/Resources/public/js`;
            $output->writeln("moved $dir/javascript to $dir/Resources/public/js - REMEMBER TO REFACTOR TEMPLATE pageaddvar calls!\n");
        }

        if (is_dir($dir.'/images')) {
            `git mv $dir/images $dir/Resources/public`;
            $output->writeln("moved $dir/images to $dir/Resources/public/images\n");
        }

        if (is_dir($dir.'/docs')) {
            `git mv $dir/docs $dir/Resources/docs`;
            $output->writeln("moved $dir/docs to $dir/Resources/public/docs\n");
        }

        if (is_dir($dir.'/locale')) {
            `git mv $dir/locale $dir/Resources`;
            $output->writeln("moved $dir/locale to $dir/Resources/public/locale\n");
        }

        if (is_dir($dir.'/templates')) {
            `git mv $dir/templates $dir/Resources/views`;
            $output->writeln("moved $dir/templates to $dir/Resources/public/views\n");
        }
    }
}

$application = new Application();
$application->add(new ControllerActionCommand());
$application->add(new MigrateResourceStructure());
$application->run();
