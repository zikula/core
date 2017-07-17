<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zikula\Composer\LessGenerator;

class LessCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build:generate_less')
            ->setDescription('Generates Bootstrap Less file')
            ->addOption('write-to', null, InputOption::VALUE_REQUIRED, 'Where to dump the generated file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        LessGenerator::generateCombinedBootstrapFontAwesomeCSS($input->getOption('write-to'));
    }
}
