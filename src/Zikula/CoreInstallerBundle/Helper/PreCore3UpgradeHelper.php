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

    public function preUpgrade(): bool
    {
        if (!file_exists($this->projectDir . '/config/services_custom.yaml')) {
            throw new FileNotFoundException(sprintf('Could not find file %s', $this->projectDir . '/config/services_custom.yaml'));
        }
        $yamlHelper = new YamlDumper($this->projectDir . '/config', 'services_custom.yaml');
        $params = $yamlHelper->getParameters();
        if (isset($params['core_installed_version']) && version_compare($params['core_installed_version'], '3.0.0', '<')) {
            $params['database_driver'] = mb_substr($params['database_driver'], 4); // remove pdo_ prefix
            (new DbCredsHelper($this->projectDir))->writeDatabaseDsn($params);
            (new LocalDotEnvHelper($this->projectDir))->writeLocalEnvVars(['ZIKULA_INSTALLED' => $params['core_installed_version']]);
            unset($params['core_installed_version']);
            $params['datadir'] = 'public/uploads';
            $params['upgrading'] = true;
            $params['installed'] = '%env(ZIKULA_INSTALLED)%';
            $params['zikula_asset_manager.combine'] = false;
            $yamlHelper->setParameters($params);

            return true;
        }

        return false;
    }
}
