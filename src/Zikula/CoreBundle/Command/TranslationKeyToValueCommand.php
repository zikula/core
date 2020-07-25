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

namespace Zikula\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class TranslationKeyToValueCommand extends Command
{
    protected static $defaultName = 'zikula:translation:keytovalue';

    private $defaultTransPath;

    public function __construct(string $defaultTransPath = null)
    {
        parent::__construct();
        $this->defaultTransPath = $defaultTransPath;
    }

    protected function configure()
    {
        $this
            ->setDescription('update translation files to remove null values and replace them with the key')
            ->addArgument('bundle', InputArgument::OPTIONAL, 'The bundle name or directory where to load the messages')
            ->setHelp(
                <<<'EOF'
The <info>%command.name%</info> command transforms translation strings of a given
bundle or the default translations directory. It sets null messages to the value of the key.

It is recommended to run <info>php bin/console translation:extract</info> first.

Example running against a Bundle (AcmeBundle)

  <info>php %command.full_name% AcmeBundle</info>

Example running against default messages directory

  <info>php %command.full_name%</info>
EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var KernelInterface $kernel */
        $kernel = $this->getApplication()->getKernel();
        $transPaths = [];
        if ($this->defaultTransPath) {
            $transPaths[] = $this->defaultTransPath;
        }
        $currentName = 'default directory';
        // Override with provided Bundle info (this section copied from symfony's translation:update command)
        if (null !== $input->getArgument('bundle')) {
            try {
                $foundBundle = $kernel->getBundle($input->getArgument('bundle'));
                $bundleDir = $foundBundle->getPath();
                $transPaths = [is_dir($bundleDir . '/Resources/translations') ? $bundleDir . '/Resources/translations' : $bundleDir . '/translations'];
                $currentName = $foundBundle->getName();
            } catch (\InvalidArgumentException $e) {
                // such a bundle does not exist, so treat the argument as path
                $path = $input->getArgument('bundle');
                $transPaths = [$path . '/translations'];
                if (!is_dir($transPaths[0]) && !isset($transPaths[1])) {
                    throw new InvalidArgumentException(sprintf('"%s" is neither an enabled bundle nor a directory.', $transPaths[0]));
                }
            }
        }
        $io->title('Translation Messages Key to Value Transformer');
        $io->comment(sprintf('Transforming translation files for "<info>%s</info>"', $currentName));
        $finder = new Finder();
        $fs = new Filesystem();
        foreach ($finder->files()->in($transPaths)->name(['*.yaml', '*.yml']) as $file) {
            $io->text(sprintf('<comment>Parsing %s</comment>', $file->getBasename()));
            $messages = Yaml::parseFile($file->getRealPath());
            foreach ($messages as $key => $message) {
                if (null === $message) {
                    $messages[$key] = $key;
                }
            }
            $io->text(sprintf('<info>Dumping %s</info>', $file->getBasename()));
            ksort($messages); // sort the messages by key
            $fs->dumpFile($file->getRealPath(), Yaml::dump($messages));
        }

        $io->success('Success!');

        return Command::SUCCESS;
    }
}
