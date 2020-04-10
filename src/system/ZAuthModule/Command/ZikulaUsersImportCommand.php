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

namespace Zikula\ZAuthModule\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;
use Zikula\ZAuthModule\Helper\FileIOHelper;

class ZikulaUsersImportCommand extends Command
{
    protected static $defaultName = 'zikula:users:import';

    /**
     * @var FileIOHelper
     */
    private $ioHelper;

    public function __construct(FileIOHelper $ioHelper)
    {
        $this->ioHelper = $ioHelper;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Import a list of users.')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to text file containing list of users.')
            ->addOption('delimiter', 'd', InputOption::VALUE_REQUIRED, 'Field delimiter (default = ,).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('path');
        $delimiter = $input->getOption('delimiter') ?? ',';

        $file = new File($path);
        $error = $this->ioHelper->importUsersFromFile($file, $delimiter);
        if (empty($error)) {
            $createdUsers = $this->ioHelper->getCreatedUsers();
            $io->success(sprintf('Import successful. %d users imported', count($createdUsers)));

            return 0;
        }
        $io->error($error);

        return 1;
    }
}
