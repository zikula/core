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

namespace Zikula\ExtensionsModule\ModuleInterface\MultiHook;

interface EntryProviderInterface
{
    /**
     * Returns the name of this entry provider.
     */
    public function getName(): string;

    /**
     * Returns the icon name (FontAwesome icon code suffix, e.g. "pencil").
     */
    public function getIcon(): string;

    /**
     * Returns the title of this entry provider.
     */
    public function getTitle(): string;

    /**
     * Returns the description of this entry provider.
     */
    public function getDescription(): string;

    /**
     * Returns an extended plugin information shown on settings page.
     */
    public function getAdminInfo(): string;

    /**
     * Returns whether this entry provider is active or not.
     */
    public function isActive(): bool;

    /**
     * Returns entries for given entry types.
     */
    public function getEntries(array $entryTypes = []): array;

    /**
     * Return the name of the providing bundle.
     */
    public function getBundleName(): string;
}
