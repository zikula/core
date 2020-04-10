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

namespace Zikula\ExtensionsModule\Collector;

use Zikula\ExtensionsModule\Installer\ExtensionInstallerInterface;

class InstallerCollector
{
    /**
     * @var ExtensionInstallerInterface[]
     */
    private $installers;

    public function __construct(iterable $installers = [])
    {
        $this->installers = [];
        foreach ($installers as $installer) {
            $this->add($installer);
        }
    }

    public function add(ExtensionInstallerInterface $block): void
    {
        $this->installers[get_class($block)] = $block;
    }

    public function has(string $id): ?bool
    {
        return isset($this->installers[$id]);
    }

    public function get(string $id): ?ExtensionInstallerInterface
    {
        return $this->installers[$id] ?? null;
    }
}
