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

namespace Zikula\ExtensionsModule\Api\ApiInterface;

use Zikula\ExtensionsModule\Entity\ExtensionEntity;

/**
 * Interface CapabilityInterface
 * Constant strings for use in the Core.
 * in composer.json extra/zikula/capabilities definition, use the actual string.
 */
interface CapabilityApiInterface
{
    /**
     * Extension implements Categorizable methods
     * composer.json definition requires an array of categorizable Entities e.g.
     *     "categorizable": {"entities": ["Acme\\FooModule\\Entity\\FooEntity", "Acme\\FooModule\\Entity\\BarEntity"]}
     */
    public const CATEGORIZABLE = 'categorizable';

    /**
     * Extension provides a user interface.
     * composer.json definition requires "route" e.g.
     *     "user": {"route": "acmefoomodule_user_index"}
     */
    public const USER = 'user';

    /**
     * Extension provides an admin interface.
     * composer.json definition requires "route" e.g.
     *     "admin": {"route": "acmefoomodule_admin_index"}
     */
    public const ADMIN = 'admin';

    /**
     * Get all the Extensions with a requested capability.
     *
     * @return ExtensionEntity[]
     */
    public function getExtensionsCapableOf(string $capability): iterable;

    /**
     * Determine if extension is capable of requested capability.
     * Returns capability array if capability is true.
     *
     * @return array|bool capability definition or false
     */
    public function isCapable(string $extensionName, string $requestedCapability);

    /**
     * Get the capabilities array of an extension.
     */
    public function getCapabilitiesOf(string $extensionName): array;
}
