<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Response
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\LinkContainer;

/**
 * Interface LinkContainerInterface
 * @package Zikula\Core\LinkContainer
 *
 * This interface is used to implement the Extension LinkContainer
 */
interface LinkContainerInterface
{
    const EVENT_NAME = 'zikula.link_collector';
    const TYPE_ADMIN = 'admin';
    const TYPE_USER = 'user';

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
