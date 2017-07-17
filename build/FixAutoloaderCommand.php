<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixAutoloaderCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build:fix_autoloader')
            ->setDescription('Fixes autoloader paths')
            ->addUsage('my/package/path/vendor')
            ->addArgument('vendor-dir', InputArgument::REQUIRED, 'Vendors dir, e.g. src/vendor');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getArgument('vendor-dir');
        $progress = new ProgressBar($output, 3);
        $progress->start();

        self::fix($dir, $progress);
    }

    public static function fix($dir, ProgressBar $progress)
    {
        // fix paths in composer autoloader files removing src/ from paths
        $composerFiles = [
            'autoload_classmap.php',
            'autoload_namespaces.php',
            'autoload_real.php',
            'autoload_files.php',
            'autoload_psr4.php',
            'autoload_static.php'
        ];
        foreach ($composerFiles as $file) {
            $file = "$dir/composer/$file";
            $content = file_get_contents($file);
            $content = str_replace("baseDir . '/src/", "baseDir . '/", $content);
            $content = str_replace('dirname(dirname($vendorDir))', 'dirname($vendorDir)', $content);
            $content = str_replace("__DIR__ . '/../../..' . '/src", "__DIR__ . '/../..' . '", $content);
            file_put_contents($file, $content);
            $progress->advance();
        }
    }
}
