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

namespace Zikula\ExtensionsModule\Twig\Extension\SimpleFunction;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class ModuleLinksFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts module links.
     *
     * Example: {( moduleLinks() }}
     *
     * @param string $type Links type admin or user
     * @param array $links Array with menu links (text, url, title, id, class, disabled) (optional)
     * @param string $modName Module name to display links for (optional)
     * @param string $menuId ID for the unordered list (optional)
     * @param string $menuClass Class for the unordered list (optional)
     * @param string $itemClass Class for li element of unordered list
     * @param string $first Class for the first element (optional)
     * @param string $last Class for the last element (optional)
     * @param string $template Template name to use instead of default (optional)
     * @return string
     */
    public function display(
        string $type = 'user',
        array $links = [],
        string $modName = '',
        string $menuId = '',
        string $menuClass = '',
        string $itemClass = '',
        string $first = '',
        string $last = '',
        string $template = ''
    ): string {
        $ref = new ControllerReference('ZikulaExtensionsModule:ExtensionsInterface:links', [
            'type' => $type,
            'links' => $links,
            'modname' => $modName,
            'menuid' => $menuId,
            'menuclass' => $menuClass,
            'itemclass' => $itemClass,
            'first' => $first,
            'last' => $last,
            'template' => $template
        ]);

        return $this->handler->render($ref);
    }
}
