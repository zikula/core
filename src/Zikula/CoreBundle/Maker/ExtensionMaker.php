<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
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
use Symfony\Component\Filesystem\Filesystem;
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

    public function __construct(ZikulaHttpKernelInterface $kernel, FileManager $fileManager)
    {
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
            ->addArgument('type', InputArgument::OPTIONAL, sprintf('Choose a extension type (<fg=yellow>module or theme</>)'))
            ->setHelp(file_get_contents(dirname(__DIR__) . '/Resources/help/ExtensionMaker.txt'))
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        try {
            $namespace = Validators::validateBundleNamespace($input->getArgument('namespace'));
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return 1;
        }
        $type = 'theme' === trim(mb_strtolower($input->getArgument('type'))) ? 'Theme' : 'Module';
        $this->localGenerator = new Generator($this->fileManager, $namespace);

        $this->createDirAndAutoload($namespace, $type);
        $bundleClass = $this->generateBundleClass($namespace, $type);
        $this->generateDIExtensionClass($namespace);
        $this->generateFiles($namespace, $type, $bundleClass);
        $this->generateBlankFiles();

        $this->localGenerator->writeChanges();
        $this->writeSuccessMessage($io);
        $io->warning(sprintf('In order to use other make:foo commands, you must change the root_namespace value in /config/packages/dev/maker.yaml to %s.', $namespace . $type));

        return 0;
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Command::class,
            'console'
        );
    }

    private function createDirAndAutoload(string $namespace, string $type): void
    {
        $projectDir = $this->fileManager->getRootDirectory();
        $fs = new Filesystem();
        [$vendor, $extensionName] = explode('\\', $namespace, 2);
        $this->extensionPath = $projectDir . '/src/extensions/' . mb_strtolower($vendor) . '/' . $extensionName . $type;
        $fs->mkdir($this->extensionPath);
        $this->kernel->getAutoloader()->addPsr4($namespace . '\\', $this->extensionPath);
    }

    private function generateBundleClass(string $namespace, string $type): string
    {
        $bundleClassName = str_replace('\\', '', $namespace);
        $bundleClass = $this->localGenerator->createClassNameDetails(
            $bundleClassName,
            '',
            $type,
            'Invalid!'
        );
        $this->localGenerator->generateClass(
            $bundleClass->getFullName(),
            dirname(__DIR__) . '/Resources/skeleton/extension/BundleClass.tpl.php',
            [
                'namespace' => $namespace,
                'type' => $type,
                'name' => $bundleClass->getShortName(),
                'vendor' => mb_substr($namespace, 0, mb_strpos($namespace, '\\'))
            ]
        );

        return $bundleClass->getFullName();
    }

    private function generateDIExtensionClass(string $namespace): void
    {
        $shortName = str_replace('\\', '', $namespace);
        $bundleClass = $this->localGenerator->createClassNameDetails(
            $shortName,
            'DependencyInjection',
            'Extension',
            'Invalid!'
        );
        $this->localGenerator->generateClass(
            $bundleClass->getFullName(),
            dirname(__DIR__) . '/Resources/skeleton/extension/DIExtensionClass.tpl.php',
            [
                'namespace' => $namespace,
                'name' => $bundleClass->getShortName(),
                'vendor' => mb_substr($namespace, 0, mb_strpos($namespace, '\\'))
            ]
        );
    }

    private function getFilesToGenerate(): iterable
    {
        return [
            'Resources/config/services.yaml' => 'services.yaml.tpl.php',
            'README.md' => 'README.md.tpl.php',
            'composer.json' => 'composer.json.tpl.php',
        ];
    }

    private function generateFiles(string $namespace, string $type, string $bundleClass): void
    {
        foreach ($this->getFilesToGenerate() as $targetPath => $templateName) {
            $this->localGenerator->generateFile(
                $this->extensionPath . '/' . $targetPath,
                dirname(__DIR__) . '/Resources/skeleton/extension/' . $templateName,
                [
                    'namespace' => $namespace,
                    'type' => $type,
                    'vendor' => mb_substr($namespace, 0, mb_strpos($namespace, '\\')),
                    'name' => mb_substr($namespace, mb_strpos($namespace, '\\') + 1),
                    'bundleClass' => $bundleClass
                ]
            );

        }
    }

    private function generateBlankFiles(): void
    {
        $fs = new Filesystem();
        $fs->mkdir($this->extensionPath . '/Resources/doc');
        $fs->touch($this->extensionPath . '/Resources/doc/index.md');
        $fs->mkdir($this->extensionPath . '/Resources/public/css');
        $fs->touch($this->extensionPath . '/Resources/public/css/style.css');
        $fs->mkdir($this->extensionPath . '/Resources/public/images');
        $fs->touch($this->extensionPath . '/Resources/public/images/.gitkeep');
        $fs->mkdir($this->extensionPath . '/Resources/public/js');
        $fs->touch($this->extensionPath . '/Resources/public/js/.gitkeep');
    }
}
