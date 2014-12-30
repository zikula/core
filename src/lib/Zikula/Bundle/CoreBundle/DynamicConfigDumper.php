<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
