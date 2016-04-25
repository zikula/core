<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DynamicConfigDumper.
 */
class DynamicConfigDumper extends YamlDumper
{
    const CONFIG_GENERATED = 'dynamic/generated.yml';
    const CONFIG_DEFAULT = 'dynamic/default.yml';

    public function __construct($configDir)
    {
        $this->fullPath = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_GENERATED;
        $configDefaultPath = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_DEFAULT;
        $this->fs = new Filesystem();

        if (!$this->fs->exists($this->fullPath)) {
            // This class is called for the very first time. Make a copy of the default configuration file and safe
            // it as generated configuration file.
            $this->fs->copy($configDefaultPath, $this->fullPath);
        }
    }

    /**
     * Dump configuration into dynamic configuration file.
     *
     * @param array $configuration The configuration array to dump.
     */
    protected function dumpFile($configuration)
    {
        $yaml = "#This is a dynamically generated configuration file. Do not touch!\n\n" . Yaml::dump($configuration);
        $this->fs->dumpFile($this->fullPath, $yaml);
    }
}
