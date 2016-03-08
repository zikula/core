<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Twig\Extension\SimpleFunction;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class ModuleLinksFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * ModuleLinksFunction constructor.
     * @param FragmentHandler $handler
     */
    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts module links.
     *
     * Examples:
     *
     * <samp>{( moduleLinks() }}</samp>
     *
     * @param string $type Links type admin or user
     * @param string $links Array with menulinks (text, url, title, id, class, disabled) (optional)
     * @param string $modName Module name to display links for (optional)
     * @param string $menuId ID for the unordered list (optional)
     * @param string $menuClass Class for the unordered list (optional)
     * @param string $itemClass Class for li element of unordered list
     * @param string $first Class for the first element (optional)
     * @param string $last Class for the last element (optional)
     * @return string
     */
    public function display($type = 'user', $links = '', $modName = '', $menuId = '', $menuClass = '', $itemClass = '', $first = '', $last = '')
    {
        $ref = new ControllerReference('ZikulaExtensionsModule:ExtensionsInterface:links', [
            'type' => $type,
            'links' => $links,
            'modname' => $modName,
            'menuid' => $menuId,
            'menuclass' => $menuClass,
            'itemclass' => $itemClass,
            'first' => $first,
            'last' => $last
        ]);

        return $this->handler->render($ref, 'inline', []);
    }
}
