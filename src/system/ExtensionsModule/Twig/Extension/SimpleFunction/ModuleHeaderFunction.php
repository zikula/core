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

class ModuleHeaderFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * ModuleHeaderFunction constructor.
     * @param FragmentHandler $handler
     */
    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts module header.
     *
     * Examples:
     *
     * <samp>{( moduleHeader() }}</samp>
     *
     * @param string $type Type of header (defaults to 'user')
     * @param string $title Title to display in header (optional, defaults to module name)
     * @param string $titleLink Link to attach to title (optional, defaults to none)
     * @param bool $setPageTitle If set to true, {pagesetvar} is used to set page title is used to set page title
     * @param bool $insertFlashes If set to true, {insert name='getstatusmsg'} is put in front of template is put in front of template
     * @param bool $menuFirst If set to true, menu is first, then title
     * @param bool $image If set to true, module image is also displayed next to title
     * @return string
     */
    public function display($type = 'user', $title = '', $titleLink = '', $setPageTitle = false, $insertFlashes = false, $menuFirst = false, $image = false)
    {
        $ref = new ControllerReference('ZikulaExtensionsModule:ExtensionsInterface:header', [
            'type' => $type,
            'title' => $title,
            'titlelink' => $titleLink,
            'setpagetitle' => $setPageTitle,
            'insertflashes' => $insertFlashes,
            'menufirst' => $menuFirst,
            'image' => $image
        ]);

        return $this->handler->render($ref, 'inline', []);
    }
}
