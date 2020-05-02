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

namespace Zikula\Bundle\CoreInstallerBundle\Command\Install;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreInstallerBundle\Helper\StageHelper;
use Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;

class FinishCommand extends Command
{
    protected static $defaultName = 'zikula:install:finish';

    /**
     * @var bool
     */
    private $installed;

    /**
     * @var StageHelper
     */
    private $stageHelper;

    /**
     * @var AjaxInstallerStage
     */
    private $ajaxInstallerStage;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $environment;

    public function __construct(
        string $installed,
        StageHelper $stageHelper,
        AjaxInstallerStage $ajaxInstallerStage,
        TranslatorInterface $translator,
        string $environment
    ) {
        parent::__construct();
        $this->installed = '0.0.0' !== $installed;
        $this->stageHelper = $stageHelper;
        $this->ajaxInstallerStage = $ajaxInstallerStage;
        $this->translator = $translator;
        $this->environment = $environment;
    }

    protected function configure()
    {
        $this->setDescription('Call this command after zikula:install:start');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (true === $this->installed) {
            $io->error($this->translator->trans('Zikula already appears to be installed.'));

            return 1;
        }

        if ($input->isInteractive()) {
            $io->section($this->translator->trans('*** INSTALLING ***'));
            $io->comment($this->translator->trans('Configuring Zikula installation in %env% environment.', ['%env%' => $this->environment]));
        }

        // install!
        $this->stageHelper->handleAjaxStage($this->ajaxInstallerStage, $io, $input->isInteractive());

        $io->success($this->translator->trans('Install Successful!'));

        return 0;
    }
}
