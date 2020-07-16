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

namespace Zikula\Bundle\CoreInstallerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zikula\Bundle\CoreBundle\Helper\LocalDotEnvHelper;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Helper\DbCredsHelper;

class PreUpgradeCommand extends Command
{
    protected static $defaultName = 'zikula:pre-upgrade';

    private $projectDir;

    public function __construct(
        string $projectDir,
        string $name = null
    ) {
        parent::__construct($name);
        $this->projectDir = $projectDir;
    }

    protected function configure()
    {
        $this
            ->setDescription('Setup the zikula upgrade command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $yamlHelper = new YamlDumper($this->projectDir . '/config', 'services_custom.yaml');
        $params = $yamlHelper->getParameters();
        dump($params);
        $io = new SymfonyStyle($input, $output);
        if (isset($params['core_installed_version']) && version_compare($params['core_installed_version'], '3.0.0', '<')) {
            (new DbCredsHelper($this->projectDir))->writeDatabaseDsn($params);
            (new LocalDotEnvHelper($this->projectDir))->writeLocalEnvVars(['ZIKULA_INSTALLED' => $params['core_installed_version']]);
            $io->success('Success! .env.local updated with Zikula Core 2.0.x settings. Please run php bin/console zikula:upgrade to continue the upgrade process.');
        } else {
            $io->comment('There is no need to run this command unless the currently installed version is lower than 3.0.0');
        }

        return Command::SUCCESS;
    }
}
