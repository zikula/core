<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
     * Extension provides required authentication methods.
     * composer.json definition requires version e.g.
     *     "authentication": {"version": "1.0"}
     */
    const AUTHENTICATION = 'authentication';
    /**
     * Extension is capable of subscriber to hooks.
     * composer.json definition requires "class" e.g.
     *     "hook_subscriber": {"class": "Acme\\FooModule\\Container\\HookContainer"}
     */
    const HOOK_SUBSCRIBER = 'hook_subscriber';
    /**
     * Extension provides hooks to which other extensions may subscribe.
     * composer.json definition requires "class" e.g.
     *     "hook_provider": {"class": "Acme\\FooModule\\Container\\HookContainer"}
     */
    const HOOK_PROVIDER = 'hook_provider';
    /**
     * Extension is capable of subscribing to its own hooks.
     * composer.json definition requires boolean as additional key in 'hook_subscriber' key e.g.
     *     "hook_subscriber": {"class": "Acme\\FooModule\\Container\\HookContainer", "subscribe_own": true}
     */
    const HOOK_SUBSCRIBE_OWN = 'subscribe_own';
    /**
     * Extension implements required profile methods.
     * composer.json definition requires version e.g.
     *     "profile": {"version": "1.0"}
     */
    const PROFILE = 'profile';
    /**
     * Extension implements required messaging methods.
     * composer.json definition requires version e.g.
     *     "message": {"version": "1.0"}
     */
    const MESSAGE = 'message';
    /**
     * Extension implements Searchable methods
     * composer.json definition requires "class" e.g.
     *     "searchable": {"class": "Acme\\FooModule\\Helper\\SearchHelper"}
     * The helper class must extend \Zikula\SearchModule\AbstractSearchable
     */
    const SEARCHABLE = 'searchable';
    /**
     * Extension implements Categorizable methods
     * composer.json definition requires an array of categorizable Entities e.g.
     *     "categorizable": {"entities": ["Acme\\FooModule\\Entity\\FooEntity", "Acme\\FooModule\\Entity\\BarEntity"]}
     */
    const CATEGORIZABLE = 'categorizable';
    /**
     * Extension provides a user interface.
     * composer.json definition requires "route" e.g.
     *     "user": {"route": "acmefoomodule_user_index"}
     */
    const USER = 'user';
    /**
     * Extension provides an admin interface.
     * composer.json definition requires "route" e.g.
     *     "admin": {"route": "acmefoomodule_admin_index"}
     */
    const ADMIN = 'admin';

    /**
     * Get all the Extensions with a requested capability.
     * @param string $capability
     * @return ExtensionEntity[]
     */
    public function getExtensionsCapableOf($capability);

    /**
     * Determine if extension is capable of requested capability.
     * Returns capability array if capability is true.
     * @param string $extensionName
     * @param string $requestedCapability
     * @return array|bool capability definition or false
     */
    public function isCapable($extensionName, $requestedCapability);

    /**
     * Get the capabilities array of an extension.
     * @param string $extensionName
     * @return array capablities of extension
     */
    public function getCapabilitiesOf($extensionName);
}
