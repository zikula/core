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

namespace Zikula\Bundle\CoreInstallerBundle\Command\Install;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreInstallerBundle\Command\AbstractCoreInstallerCommand;
use Zikula\Bundle\CoreInstallerBundle\Helper\StageHelper;
use Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;

class FinishCommand extends AbstractCoreInstallerCommand
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var string
     */
    private $installed;

    /**
     * @var StageHelper
     */
    private $stageHelper;

    public function __construct(
        string $environment,
        bool $installed,
        StageHelper $stageHelper,
        TranslatorInterface $translator
    ) {
        parent::__construct($translator);
        $this->installed = $installed;
        $this->environment = $environment;
        $this->stageHelper = $stageHelper;
    }

    protected function configure()
    {
        $this
            ->setName('zikula:install:finish')
            ->setDescription('Call this command after zikula:install:start')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (true === $this->installed) {
            $io->error($this->translator->trans('Zikula already appears to be installed.'));

            return 1;
        }

        $io->section($this->translator->trans('*** INSTALLING ***'));
        $io->comment($this->translator->trans('Configuring Zikula installation in %env% environment.', ['%env%' => $this->environment]));

        // install!
        $ajaxStage = new AjaxInstallerStage();
        $ajaxStage->setTranslator($this->translator);
        $this->stageHelper->handleAjaxStage($ajaxStage, $io);

        $io->success($this->translator->trans('INSTALL COMPLETE!'));

        return 0;
    }
}
