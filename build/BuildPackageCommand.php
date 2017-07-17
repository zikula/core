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

class BuildPackageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build:package')
            ->setDescription('Packages Zikula')
            ->addUsage('my-buildname path/to/my/build/dir path/to/my/source/dir')
            ->addArgument('name', InputArgument::REQUIRED, 'Build name')
            ->addArgument('source-dir', InputArgument::REQUIRED, 'Build dir')
            ->addArgument('build-dir', InputArgument::REQUIRED, 'Source dir');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $sourceDir = $input->getArgument('source-dir');
        $buildDir = $input->getArgument('build-dir');

        $progress = new ProgressBar($output, 17);
        $progress->start();

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
        FixAutoloaderCommand::fix("$buildDir/$name/vendor", $progress);

        $writableArray = [
            "$buildDir/$name/var/cache",
            "$buildDir/$name/var/logs",
            "$buildDir/$name/web/uploads",
        ];
        $filesystem->chmod($writableArray, 0777);
        $progress->advance();

        chdir($buildDir);
        $finder = new Finder();
        $finder
            ->in($name)
            ->files()
            ->ignoreDotFiles(false);

        $allFiles = [];
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
