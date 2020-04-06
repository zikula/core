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
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreInstallerBundle\Command\AbstractCoreInstallerCommand;
use Zikula\Bundle\CoreInstallerBundle\Helper\StageHelper;
use Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;

class FinishCommand extends AbstractCoreInstallerCommand
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

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        string $installed,
        StageHelper $stageHelper,
        AjaxInstallerStage $ajaxInstallerStage,
        TranslatorInterface $translator
    ) {
        $this->installed = '0.0.0' !== $installed;
        $this->stageHelper = $stageHelper;
        $this->ajaxInstallerStage = $ajaxInstallerStage;
        parent::__construct($kernel, $translator);
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

        $io->section($this->translator->trans('*** INSTALLING ***'));
        $io->comment($this->translator->trans('Configuring Zikula installation in %env% environment.', ['%env%' => $this->kernel->getEnvironment()]));

        // install!
        $this->stageHelper->handleAjaxStage($this->ajaxInstallerStage, $io);

        $io->success($this->translator->trans('INSTALL COMPLETE!'));

        return 0;
    }
}
