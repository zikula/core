<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
