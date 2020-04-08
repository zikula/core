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
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;

class SetNamespaceMaker extends AbstractMaker
{
    /**
     * @var DynamicConfigDumper
     */
    private $configDumper;

    public function __construct(
        DynamicConfigDumper $configDumper
    ) {
        $this->configDumper = $configDumper;
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
            ->setHelp('Set the maker namespace config <info>php %command.full_name% Acme/BlogModule</info>')
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        try {
            $namespace = Validators::validateBundleNamespace($input->getArgument('namespace'), true);
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return 1;
        }
        $this->configDumper->setConfiguration(
            'maker',
            [
                'root_namespace' => $namespace,
            ],
            true
        );
        $io->success(sprintf('The `config/generated_dev.yaml` file has been updated to set `maker:root_namespace` value to %s.', $namespace));

        return 0;
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Command::class,
            'console'
        );
        $dependencies->addClassDependency(
            DynamicConfigDumper::class,
            'zikula/core-bundle'
        );
    }
}
