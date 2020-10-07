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

namespace Zikula\Bundle\CoreBundle\Helper;

use Symfony\Component\Filesystem\Filesystem;
use function Symfony\Component\String\s;

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
        $localEnvPath = $this->projectDir . '/.env.local';
        $fileSystem = new Filesystem();
        $vars = [];
        if (!$override && $fileSystem->exists($localEnvPath)) {
            $content = explode("\n", file_get_contents($localEnvPath));
            foreach ($content as $line) {
                if (empty($line) || '#' === $line[0]) {
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
        $fileSystem->dumpFile($localEnvPath, $this->varsToString($vars));
    }

    private function varsToString(array $vars): string
    {
        $lines = [];
        foreach ($vars as $key => $value) {
            $value = s((string) $value);
            if ($value->startsWith('!')) {
                $value = $value->trimStart('!')->toString();
            } else {
                $quote = $value->startsWith('\'') || $value->startsWith('"') ? '\'' : '';
                $value = !empty($quote) ? $value->trim('\'')->trim('"') : $value;
                $value = $quote . urlencode($value->toString()) . $quote;
            }
            $lines[] = $key . '=' . $value;
        }

        return trim(implode("\n", $lines));
    }
}
