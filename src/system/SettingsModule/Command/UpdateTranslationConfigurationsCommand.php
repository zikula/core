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

namespace Zikula\SettingsModule\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\SettingsModule\Helper\TranslationConfigHelper;

class UpdateTranslationConfigurationsCommand extends Command
{
    protected static $defaultName = 'zikula:translation:updateconfig';

    /**
     * @var TranslationConfigHelper
     */
    private $translationConfigHelper;

    public function __construct(TranslationConfigHelper $translationConfigHelper)
    {
        parent::__construct();
        $this->translationConfigHelper = $translationConfigHelper;
    }

    protected function configure()
    {
        $this
            ->setDescription('Updates translation configurations based on currently installed and activated extensions.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->translationConfigHelper->updateConfiguration();

        $io->success('Complete');

        return Command::SUCCESS;
    }
}
