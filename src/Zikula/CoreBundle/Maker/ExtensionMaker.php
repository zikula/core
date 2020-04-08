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
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
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
     * @var DynamicConfigDumper
     */
    private $configDumper;

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
        FileManager $fileManager,
        DynamicConfigDumper $configDumper
    ) {
        $this->kernel = $kernel;
        $this->fileManager = $fileManager;
        $this->configDumper = $configDumper;
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
        $this->localGenerator = new Generator($this->fileManager, $namespace . $type);

        $this->createDirAndAutoload($namespace . $type);
        $bundleClass = $this->generateClasses($namespace, $type);
        $this->generateFiles($namespace, $type, $bundleClass);
        $this->generateBlankFiles();

        $this->localGenerator->writeChanges();
        $this->writeSuccessMessage($io);
        $this->configDumper->setConfiguration('maker',
            [
                'root_namespace' => $namespace . $type,
            ]
        );
        $io->success(sprintf('The `config/generated.yaml` file has been updated to set `maker:root_namespace` value to %s.', $namespace . $type));

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
            DynamicConfigDumper::class,
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
        [$vendor, $extensionName] = explode('\\', $namespace, 2);

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
                    'vendor' => mb_substr($namespace, 0, mb_strpos($namespace, '\\'))
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
        $fs->mkdir($this->extensionPath . '/Resources/doc/help/en');
        $fs->touch($this->extensionPath . '/Resources/doc/help/en/README.md');
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
