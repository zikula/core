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
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\CoreBundle\Bundle\Initializer\InitializableBundleInterface;

/**
 * Command that performs setup tasks for a given bundle implementing InitializableBundleInterface.
 */
#[AsCommand(
    name: 'zikula:init-bundle',
    description: 'Performs setup tasks for a given initializable bundle.',
    help: <<<TXT
        The <info>%command.name%</info> command performs setup tasks for a given bundle implementing <info>InitializableBundleInterface</info>.

        <info>php %command.full_name% AcmeFooBundle</info>
        TXT
)]
class InitBundleCommand
{
    public function __construct(private readonly KernelInterface $kernel)
    {
    }

    /**
     * @throws InvalidArgumentException When the bundle is not an initializable bundle
     */
    public function __invoke(
        SymfonyStyle $io,
        #[Argument(name: 'bundle', description: 'The bundle name')] string $bundleName
    ): int {
        $bundle = $this->kernel->getBundle($bundleName);
        if (!($bundle instanceof InitializableBundleInterface)) {
            throw new InvalidArgumentException(sprintf('"%s" is not an initializable bundle.', $bundleName));
        }

        $bundle->getInitializer()->init();

        return Command::SUCCESS;
    }
}
