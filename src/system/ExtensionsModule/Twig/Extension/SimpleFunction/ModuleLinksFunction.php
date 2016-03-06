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

class ModuleLinksFunction
{
    private $handler;

    /**
     * ModuleLinksFunction constructor.
     */
    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts module links.
     *
     *
     * Available parameters:
     * type Links type admin or user
     * links Array with menulinks (text, url, title, id, class, disabled) (optional)
     * modname Module name to display links for (optional)
     * menuid ID for the unordered list (optional)
     * menuclass Class for the unordered list (optional)
     * itemclass Class for li element of unordered list
     * first Class for the first element (optional)
     * last Class for the last element (optional)
     *
     * Examples:
     *
     * <samp>{( moduleLinks() }}</samp>
     *
     * @return string
     */
    public function display($type = 'user', $links = '', $modname = '', $menuid = '', $menuclass = '', $itemclass = '', $first = '', $last = '')
    {
        $ref = new ControllerReference('ZikulaExtensionsModule:ExtensionsInterface:links', [
            'type' => $type,
            'links' => $links,
            'modname' => $modname,
            'menuid' => $menuid,
            'menuclass' => $menuclass,
            'itemclass' => $itemclass,
            'first' => $first,
            'last' => $last
        ]);

        return $this->handler->render($ref, 'inline', []);
    }
}
