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

namespace Zikula\ExtensionsModule\ModuleInterface\MultiHook;

interface NeedleInterface
{
    /**
     * Returns the name of this needle.
     */
    public function getName(): string;

    /**
     * Returns the icon name (FontAwesome icon code suffix, e.g. "pencil").
     */
    public function getIcon(): string;

    /**
     * Returns the title of this needle.
     */
    public function getTitle(): string;

    /**
     * Returns the description of this needle.
     */
    public function getDescription(): string;

    /**
     * Returns usage information shown on settings page.
     */
    public function getUsageInfo(): string;

    /**
     * Returns whether this needle is active or not.
     */
    public function isActive(): bool;

    /**
     * Returns whether this needle is case sensitive or not.
     */
    public function isCaseSensitive(): bool;

    /**
     * Returns the needle subject entries.
     *
     * @return string[]
     */
    public function getSubjects(): array;

    /**
     * Applies the needle functionality.
     */
    public function apply(string $needleId, string $needleText): string;

    /**
     * Return the name of the providing bundle.
     */
    public function getBundleName(): string;
}
