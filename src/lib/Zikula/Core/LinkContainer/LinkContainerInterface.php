<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
    const EVENT_NAME = 'zikula.link_collector';
    const TYPE_ADMIN = 'admin';
    const TYPE_USER = 'user';
    const TYPE_ACCOUNT = 'account';

    /**
     * Return the name of the providing bundle.
     * @return string
     */
    public function getBundleName();

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
     * @param string $type
     * @return array
     */
    public function getLinks($type = self::TYPE_ADMIN);
}
