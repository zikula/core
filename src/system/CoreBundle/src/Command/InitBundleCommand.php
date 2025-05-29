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

namespace Zikula\CoreBundle\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\CoreBundle\Bundle\Initializer\InitializableBundleInterface;

/**
 * Command that performs setup tasks for a given bundle implementing InitializableBundleInterface.
 */
#[AsCommand(name: 'zikula:init-bundle', description: 'Performs setup tasks for a given initializable bundle.')]
class InitBundleCommand extends Command
{
    public function __construct(private readonly KernelInterface $kernel)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('bundle', InputArgument::REQUIRED, 'The bundle name')
            ->setHelp(
                <<<'EOT'
The <info>%command.name%</info> command performs setup tasks for a given bundle implementing <info>InitializableBundleInterface</info>.

<info>php %command.full_name% AcmeFooBundle</info>

EOT
            );
    }

    /**
     * @throws InvalidArgumentException When the bundle is not an initializable bundle
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bundleName = $input->getArgument('bundle');
        $bundle = $this->kernel->getBundle($bundleName);
        if (!($bundle instanceof InitializableBundleInterface)) {
            throw new InvalidArgumentException(sprintf('"%s" is not an initializable bundle.', $bundleName));
        }

        $bundle->getInitializer()->init();

        return Command::SUCCESS;
    }
}
