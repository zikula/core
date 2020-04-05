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

namespace Zikula\Bundle\CoreBundle\Helper;

use Symfony\Component\Filesystem\Filesystem;

class LocalDotEnvHelper
{
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(
        string $projectDir
    ) {
        $this->projectDir = $projectDir;
    }

    /**
     * Add $newVars to the .env.local file. If true === $override, remove existing values.
     * Prepend value with '!' to disable pseudo-urlencoding that value
     */
    public function writeLocalEnvVars(array $newVars, bool $override = false): void
    {
        $localEnvDir = $this->projectDir . '/.env.local';
        $fileSystem = new Filesystem();
        $vars = [];
        if (!$override && $fileSystem->exists($localEnvDir)) {
            $content = explode("\n", file_get_contents($localEnvDir));
            foreach ($content as $line) {
                if (empty($line)) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                if (isset($newVars[$key])) {
                    unset($vars[$key]); // removing the old $key preserves the order of the $newVars when set
                } else {
                    $vars[$key] = '!' . $value; // never encode existing values
                }
            }
        }
        $vars = $vars + array_diff_assoc($newVars, $vars);
        $fileSystem->dumpFile($localEnvDir, $this->varsToString($vars));
    }

    private function varsToString(array $vars): string
    {
        $lines = [];
        foreach ($vars as $key => $value) {
            $value = '!' === mb_substr((string) $value, 0, 1) ? mb_substr((string) $value, 1) : str_replace(['#', '@', '(', ')'], ['%23', '%40', '%28', '%29'], (string) $value);
            $lines[] = $key . '=' . $value;
        }

        return trim(implode("\n", $lines));
    }
}
