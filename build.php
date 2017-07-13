#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

require 'src/vendor/autoload.php';

class GenerateVendorDocCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build:generate_vendor_doc')
            ->setDescription('Generates a file containing all the vendors and the installed version.')
            ->addOption('write-to', null, InputOption::VALUE_REQUIRED, 'Where to dump the generated file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Reading composer vendors.');
        $packages = json_decode(file_get_contents('composer.lock'), true);
        $packages = $packages['packages'];

        $output->writeln('Generating output');

        $typeOrder = [
            'zikula-module' => 'Zikula Modules',
            'zikula-theme' => 'Zikula Themes',
            'symfony-bundle' => 'Symfony Bundles',
            'component' => 'Web Components',
            'library' => 'Other PHP libraries',
            'composer-installer' => 'Composer Installers',
            'composer-plugin' => 'Composer Plugins'
        ];
        $types = array_keys($typeOrder);
        usort($packages, function ($a, $b) use ($types) {
            return array_search($a['type'], $types) - array_search($b['type'], $types);
        });

        $content = '';
        $currentType = '';
        $authors = [];
        foreach ($packages as $package) {
            if ($currentType != $package['type']) {
                if ($currentType != '') {
                    $content .= "\n";
                }
                $content .= $typeOrder[$package['type']] . "\n";
                $content .= str_repeat('-', strlen($typeOrder[$package['type']])) . "\n";
                $currentType = $package['type'];
            }
            $content .= "- **" . $package['name'] . "** `" . $package['version'] . "`";
            if (isset($package['license'])) {
                $content .= ", License: `" . implode(', ', $package['license']) . "`\n";
            } else {
                $content .= "\n";
            }
            if (isset($package['description'])) {
                $content .= "  *" . $package['description'] . "*\n";
            }
            if (isset($package['authors'])) {
                $authors = array_merge($authors, $package['authors']);
            }
        }

        $content .= "\n\n";
        $content .= "These are the main authors of all of the projects supporting Zikula\n";
        $content .= "-------------------------------------------------------------------\n";

        $tmp = [];
        foreach ($authors as $k => $author) {
            if (in_array($author['name'], $tmp)) {
                unset($authors[$k]);
                continue;
            }
            $tmp[] = $author['name'];
        }
        foreach ($authors as $author) {
            $content .= "- **" . $author['name'] . "**";
            if (isset($author['homepage'])) {
                $content .= " " . $author['homepage'];
            }
            if (isset($author['email'])) {
                $content .= " *" . $author['email'] . "*";
            }
            $content .= "\n";
        }

        $output->writeln('Dumping vendors to ' . $input->getOption('write-to'));

        file_put_contents($input->getOption('write-to'), $content);
    }
}

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

/**
 * UNUSED COMMAND
 * Class BuildPackageCommand
 */
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
        \Zikula\Composer\LessGenerator::generateCombinedBootstrapFontAwesomeCSS($input->getOption('write-to'));
    }
}

$application = new Application();
$application->add(new BuildPackageCommand());
$application->add(new PurgeVendorsCommand());
$application->add(new FixAutoloaderCommand());
$application->add(new GenerateVendorDocCommand());
$application->add(new LessCommand());
$application->run();
