#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;

require 'src/vendor/autoload.php';

class PurgeVendorsCommand extends \Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('build:purge_vendors')
            ->setDescription('Purges tests from vendors')
            ->addOption('vendor-dir', null, InputOption::VALUE_REQUIRED, 'Vendors dir, e.g. src/vendor');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getOption('vendor-dir');
        if (!$dir) {
            $output->writeln("<error>--vendor-dir= is required</error>");
            exit(1);
        }

        /** @var ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, 4);

        PurgeVendorsCommand::cleanVendors($dir, $progress);
    }

    public static function cleanVendors($dir, ProgressHelper $progress)
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

        $paths = array();
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

class BuildPackageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build:package')
            ->setDescription('Packages Zikula')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Build name')
            ->addOption('source-dir', null, InputOption::VALUE_REQUIRED, 'Build dir')
            ->addOption('build-dir', null, InputOption::VALUE_REQUIRED, 'Source dir');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getOption('name');
        if (!$name) {
            $output->writeln("<error>--name= is required</error>");
            exit(1);
        }
        $sourceDir = $input->getOption('source-dir');
        if (!$sourceDir) {
            $output->writeln("<error>--source-dir= is required</error>");
            exit(1);
        }
        $buildDir = $input->getOption('build-dir');
        if (!$buildDir) {
            $output->writeln("<error>--build-dir= is required</error>");
            exit(1);
        }

        /** @var ProgressHelper $progress */
        $progress = $this->getHelperSet()->get('progress');
        $progress->start($output, 14);

        $filesystem = new Filesystem();

        $pwd = getcwd();
        if (is_dir($buildDir)) {
            system("rm -rf $buildDir");
        }

        // build env
        $filesystem->mkdir($buildDir, 0755);
        $progress->advance();

        $filesystem->mirror($sourceDir, "$buildDir/$name");
        $progress->advance();

        PurgeVendorsCommand::cleanVendors("$buildDir/$name/vendor", $progress);

        // fix paths in composer autoloader files removing src/ from paths
        $composerFiles = array(
            'autoload_classmap.php', 'autoload_namespaces.php', 'autoload_real.php',
        );
        foreach ($composerFiles as $file) {
            $file = "$buildDir/$name/vendor/composer/$file";
            $content = file_get_contents($file);
            $content = str_replace("baseDir . '/src/", "baseDir . '/", $content);
            file_put_contents($file, $content);
        }

        $writableArray = array(
            "$buildDir/$name/app/cache",
            "$buildDir/$name/app/logs",
            "$buildDir/$name/userdata",
            "$buildDir/$name/ztemp",
            "$buildDir/$name/ztemp/error_logs",
            "$buildDir/$name/ztemp/view_cache",
            "$buildDir/$name/ztemp/view_compiled",
            "$buildDir/$name/ztemp/Theme_cache",
            "$buildDir/$name/ztemp/Theme_compiled",
            "$buildDir/$name/ztemp/idsTmp",
            "$buildDir/$name/ztemp/purifierCache",
        );
        $filesystem->chmod($writableArray, 0777);
        $filesystem->chmod("$buildDir/$name/config/config.php", 0666);
        $progress->advance();

        chdir($buildDir);
        $finder = new Finder();
        $finder
            ->in($name)
            ->files()
            ->ignoreDotFiles(false);

        $allFiles = array();
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $allFiles[] = $file->getRelativePathname();
        }
        $progress->advance();

        // build zip
        $zip = new \ZipArchive();
        $fileName = "$name.zip";
        if ($zip->open($fileName, \ZipArchive::CREATE) !== true) {
            $output->writeln("<error>Error creating $fileName</error>");
        }

        foreach ($allFiles as $file) {
            $zip->addFile("$name/$file");
        }
        $progress->advance();

        $zip->close();
        $progress->advance();

        // build tar
        $fileName = "$name.tar";
        system("tar cp $name > $fileName");
        $progress->advance();
        system("gzip $fileName");
        $progress->advance();

        // checksums
        $zipMd5 = md5_file("$name.zip");
        $tarMd5 = md5_file("$name.tar.gz");
        $zipSha1 = sha1_file("$name.zip");
        $tarSha1 = sha1_file("$name.tar.gz");

        $checksum = <<<CHECKSUM
-----------------md5sums-----------------
$zipMd5  $name.zip
$tarMd5  $name.tar.gz
-----------------sha1sums-----------------
$zipSha1  $name.zip
$tarSha1  $name.tar.gz
CHECKSUM;
        file_put_contents("$name-checksum.txt", $checksum);
        $progress->advance();

        // cleanup
        system("rm -rf $buildDir $name");
        chdir($pwd);
        $progress->advance();
        $progress->finish();

        $output->writeln("<info>Artifacts built in $buildDir/ folder</info>");
    }
}

$application = new Application();
$application->add(new BuildPackageCommand());
$application->add(new PurgeVendorsCommand());
$application->run();
