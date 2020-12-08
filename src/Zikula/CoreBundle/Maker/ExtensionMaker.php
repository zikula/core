<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\String\s;
use Zikula\Bundle\CoreBundle\Configurator;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;

class ExtensionMaker extends AbstractMaker
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @var Generator
     */
    private $localGenerator;

    /**
     * @var string
     */
    private $extensionPath;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        FileManager $fileManager
    ) {
        $this->kernel = $kernel;
        $this->fileManager = $fileManager;
    }

    public static function getCommandName(): string
    {
        return 'make:zikula-extension';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Creates a new zikula extension bundle')
            ->addArgument('namespace', InputArgument::OPTIONAL, sprintf('Choose a namespace (e.g. <fg=yellow>Acme\%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addArgument('type', InputArgument::OPTIONAL, 'Choose a extension type (<fg=yellow>module or theme</>)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Required to use Zikula namespace.')
            ->setHelp(file_get_contents(dirname(__DIR__) . '/Resources/help/ExtensionMaker.txt'))
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        try {
            $namespace = Validators::validateBundleNamespace($input);
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return 1;
        }
        $type = 'theme' === trim(mb_strtolower($input->getArgument('type'))) ? 'Theme' : 'Module';
        $this->localGenerator = new Generator($this->fileManager, $namespace . $type);

        $this->createDirAndAutoload($namespace . $type);
        $bundleClass = $this->generateClasses($namespace, $type);
        $this->generateFiles($namespace, $type, $bundleClass);
        $this->generateBlankFiles();

        $this->localGenerator->writeChanges();
        $this->writeSuccessMessage($io);
        $configurator = new Configurator($this->kernel->getProjectDir());
        $configurator->loadPackages('core');
        $configurator->set('core', 'maker_root_namespace', $namespace . $type);
        $configurator->write();
        $io->success(sprintf('The `config/packages/core.yaml` file has been updated to set `maker_root_namespace` value to %s.', $namespace . $type));

        $io->warning(sprintf("In order to use other make:foo commands, you must install the extension!\nfirst run `php bin/console cache:clear`\nsecond run `php bin/console z:e:i %s`", $bundleClass));

        return 0;
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Command::class,
            'console'
        );
        $dependencies->addClassDependency(
            FileManager::class,
            'maker-bundle'
        );
        $dependencies->addClassDependency(
            ZikulaHttpKernelInterface::class,
            'zikula/core-bundle'
        );
        $dependencies->addClassDependency(
            Configurator::class,
            'zikula/core-bundle'
        );
    }

    private function createDirAndAutoload(string $namespace): void
    {
        $projectDir = $this->fileManager->getRootDirectory();
        $fs = new Filesystem();
        [$vendor, $extensionName] = explode('\\', $namespace, 2);
        $this->extensionPath = $projectDir . '/src/extensions/' . mb_strtolower($vendor) . '/' . $extensionName;
        $fs->mkdir($this->extensionPath);
        $this->kernel->getAutoloader()->addPsr4($namespace . '\\', $this->extensionPath);
    }

    private function getClassesToGenerate(string $namespace, string $type): iterable
    {
        $bundleClassName = str_replace('\\', '', $namespace);
        [, $extensionName] = explode('\\', $namespace, 2);

        return [
            ['name' => $bundleClassName, 'prefix' => '', 'suffix' => $type, 'template' => 'BundleClass.tpl.php'],
            ['name' => $bundleClassName, 'prefix' => 'DependencyInjection', 'suffix' => 'Extension', 'template' => 'DIExtensionClass.tpl.php'],
            ['name' => $extensionName . $type, 'prefix' => '', 'suffix' => 'Installer', 'template' => 'InstallerClass.tpl.php'],
        ];
    }

    private function generateClasses(string $namespace, string $type): string
    {
        $bundleClassFullName = '';
        foreach ($this->getClassesToGenerate($namespace, $type) as $classInfo) {
            $bundleClassNameDetails = $this->localGenerator->createClassNameDetails(
                $classInfo['name'],
                $classInfo['prefix'],
                $classInfo['suffix'],
                'Invalid!' . $classInfo['name']
            );
            $bundleClassFullName = ('' === $classInfo['prefix'] && $type === $classInfo['suffix']) ? $bundleClassNameDetails->getShortName() : $bundleClassFullName;
            $this->localGenerator->generateClass(
                $bundleClassNameDetails->getFullName(),
                dirname(__DIR__) . '/Resources/skeleton/extension/' . $classInfo['template'],
                [
                    'namespace' => $namespace . $type,
                    'type' => $type,
                    'name' => $bundleClassNameDetails->getShortName(),
                    'vendor' => s($namespace)->before('\\')->toString(),
                ]
            );
        }

        return $bundleClassFullName;
    }

    private function getFilesToGenerate(): iterable
    {
        return [
            'Resources/config/services.yaml' => 'services.yaml.tpl.php',
            'README.md' => 'README.md.tpl.php',
            'composer.json' => 'composer.json.tpl.php',
            'LICENSE.txt' => 'MIT.txt.tpl.php',
        ];
    }

    private function generateFiles(string $namespace, string $type, string $bundleClass): void
    {
        foreach ($this->getFilesToGenerate() as $targetPath => $templateName) {
            $this->localGenerator->generateFile(
                $this->extensionPath . '/' . $targetPath,
                dirname(__DIR__) . '/Resources/skeleton/extension/' . $templateName,
                [
                    'namespace' => $namespace . $type,
                    'type' => $type,
                    'vendor' => s($namespace)->before('\\')->toString(),
                    'name' => s($namespace)->after('\\')->toString(),
                    'bundleClass' => $bundleClass,
                ]
            );
        }
    }

    private function generateBlankFiles(): void
    {
        $fs = new Filesystem();
        $fs->mkdir($this->extensionPath . '/Resources/docs');
        $fs->touch($this->extensionPath . '/Resources/docs/index.md');
        $fs->mkdir($this->extensionPath . '/Resources/docs/help/en');
        $fs->touch($this->extensionPath . '/Resources/docs/help/en/README.md');
        $fs->mkdir($this->extensionPath . '/Resources/public/css');
        $fs->touch($this->extensionPath . '/Resources/public/css/style.css');
        $fs->mkdir($this->extensionPath . '/Resources/public/images');
        $fs->touch($this->extensionPath . '/Resources/public/images/.gitkeep');
        $fs->mkdir($this->extensionPath . '/Resources/public/js');
        $fs->touch($this->extensionPath . '/Resources/public/js/.gitkeep');
        $fs->mkdir($this->extensionPath . '/Resources/translations');
        $fs->touch($this->extensionPath . '/Resources/translations/.gitkeep');
        $fs->mkdir($this->extensionPath . '/Resources/views');
        $fs->touch($this->extensionPath . '/Resources/views/.gitkeep');
    }
}
