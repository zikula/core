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

namespace Zikula\Bundle\CoreBundle;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DynamicConfigDumper.
 */
class DynamicConfigDumper extends YamlDumper
{
    public const CONFIG_GENERATED = 'dynamic/generated.yml';

    public const CONFIG_DEFAULT = 'dynamic/default.yml';

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
     */
    protected function dumpFile(array $configuration = []): void
    {
        $flags = Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE; // for #2889
        $yaml = "#This is a dynamically generated configuration file. Do not touch!\n\n" . Yaml::dump($configuration, 4, 4, $flags);
        $this->fs->dumpFile($this->fullPath, $yaml);
    }
}
