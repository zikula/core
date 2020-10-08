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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use function Symfony\Component\String\s;
use Zikula\Bundle\CoreBundle\Configurator;
use Zikula\Bundle\CoreBundle\DependencyInjection\Configuration;
use Zikula\Bundle\CoreBundle\Helper\LocalDotEnvHelper;
use Zikula\Bundle\CoreBundle\YamlDumper;

class PreCore3UpgradeHelper
{
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * When upgrading from 1.x or 2.x to Core 3.x, the database connection must be
     * immediately available. In previous versions, the connection credentials are
     * stored in the (legacy) services_custom.yaml file. In Core 3.x this is now
     * stored as the env variable DATABASE_URL. This method will migrate the values
     * and a few other helpful values before the legacy file is later removed.
     * @see \Zikula\Bundle\CoreInstallerBundle\EventListener\InstallUpgradeCheckListener::checkForCore3Upgrade
     */
    public function preUpgrade(): bool
    {
        if (!file_exists($this->projectDir . '/config/services_custom.yaml')) {
            throw new FileNotFoundException(sprintf('Could not find file %s', $this->projectDir . '/config/services_custom.yaml'));
        }
        $yamlHelper = new YamlDumper($this->projectDir . '/config', 'services_custom.yaml');
        $params = $yamlHelper->getParameters();
        if (isset($params['core_installed_version']) && version_compare($params['core_installed_version'], '3.0.0', '<')) {
            $params['database_driver'] = s($params['database_driver'])->trimStart('pdo_')->toString();
            (new DbCredsHelper($this->projectDir))->writeDatabaseDsn($params);
            (new LocalDotEnvHelper($this->projectDir))->writeLocalEnvVars(['ZIKULA_INSTALLED' => $params['core_installed_version']]);
            unset($params['core_installed_version']);
            $configurator = new Configurator($this->projectDir);
            $configurator->loadPackages(['core', 'zikula_theme']);
            $configurator->set('core', 'datadir', Configuration::DEFAULT_DATADIR);
            $configurator->set('zikula_theme', 'asset_manager', ['combine' => false, 'lifetime' => '1 day', 'compress' => true, 'minify' => true]);
            $configurator->write();

            return true;
        }

        return false;
    }
}
