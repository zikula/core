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

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Zikula\ExtensionsModule\Installer\ExtensionInstallerInterface;

class InstallerCollector
{
    /**
     * @var ExtensionInstallerInterface[]
     */
    private array $installers = [];

    public function __construct(
        #[TaggedIterator('zikula.extension_installer')]
        iterable $installers
    )
    {
        foreach ($installers as $installer) {
            $this->add($installer);
        }
    }

    public function add(ExtensionInstallerInterface $installer): void
    {
        $this->installers[$installer::class] = $installer;
    }

    public function has(string $id): bool
    {
        return isset($this->installers[$id]);
    }

    public function get(string $id): ?ExtensionInstallerInterface
    {
        return $this->installers[$id] ?? null;
    }
}
