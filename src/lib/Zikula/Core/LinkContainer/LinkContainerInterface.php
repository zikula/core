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

namespace Zikula\Core\LinkContainer;

/**
 * Interface LinkContainerInterface
 *
 * This interface is used to implement the Extension LinkContainer
 */
interface LinkContainerInterface
{
    public const EVENT_NAME = 'zikula.link_collector';

    public const TYPE_ADMIN = 'admin';

    public const TYPE_USER = 'user';

    public const TYPE_ACCOUNT = 'account';

    /**
     * Return the name of the providing bundle.
     */
    public function getBundleName(): string;

    /**
     * Return an array of arrays based on the `type` parameter.
     * <code>
     * [
     *   ['url' => '/bar',
     *     'text' => 'Bar',
     *     'icon' => 'check'],
     *   ['url' => '/bar2',
     *     'text' => 'Bar 2',
     *     'icon' => 'check'],
     * ]
     * </code>
     */
    public function getLinks(string $type = self::TYPE_ADMIN): array;
}
