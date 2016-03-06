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

class ModuleHeaderFunction
{
    private $handler;

    /**
     * ModuleHeaderFunction constructor.
     */
    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts module header.
     *
     * Available parameters:
     *  type       Type of header (defaults to 'user')
     *  title      Title to display in header (optional, defaults to module name)
     *  titlelink  Link to attach to title (optional, defaults to none)
     *  setpagetitle If set to true, {pagesetvar} is used to set page title
     *  insertflashes If set to true, {insert name='getstatusmsg'} is put in front of template
     *  menufirst  If set to true, menu is first, then title
     *  image   If set to true, module image is also displayed next to title
     *
     * Examples:
     *
     * <samp>{( moduleHeader() }}</samp>
     *
     * @return string
     */
    public function display($type = 'user', $title = '', $titlelink = '', $setpagetitle = false, $insertflashes = false, $menufirst = false, $image = false)
    {
        $ref = new ControllerReference('ZikulaExtensionsModule:ExtensionsInterface:header', [
            'type' => $type,
            'title' => $title,
            'titlelink' => $titlelink,
            'setpagetitle' => $setpagetitle,
            'insertflashes' => $insertflashes,
            'menufirst' => $menufirst,
            'image' => $image
        ]);

        return $this->handler->render($ref, 'inline', []);
    }
}
