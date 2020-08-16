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
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Zikula\Bundle\CoreBundle\Configurator;

class SetNamespaceMaker extends AbstractMaker
{
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public static function getCommandName(): string
    {
        return 'make:set-namespace';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->setDescription('Sets the maker namespace config')
            ->addArgument('namespace', InputArgument::OPTIONAL, 'Choose a namespace (e.g. <fg=yellow>Acme\\BlogModule</>)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Required to use Zikula namespace.')
            ->setHelp('Set the maker namespace config <info>php %command.full_name% Acme/BlogModule</info>')
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        try {
            $namespace = Validators::validateBundleNamespace($input, true);
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return 1;
        }
        $configurator = new Configurator($this->projectDir);
        $configurator->loadPackages('core');
        $configurator->set('core', 'maker_root_namespace', $namespace);
        $configurator->write();
        $io->success(sprintf('The `config/packages/core.yaml` file has been updated to set `maker_root_namespace` value to %s.', $namespace));
        $io->newLine();
        $io->warning("In order to use other make:foo commands, you must first run `php bin/console cache:clear`");
        $io->newLine();

        return 0;
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Command::class,
            'console'
        );
        $dependencies->addClassDependency(
            Configurator::class,
            'zikula/core-bundle'
        );
    }
}
