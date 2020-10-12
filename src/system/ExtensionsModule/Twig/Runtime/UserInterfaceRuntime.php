<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Twig\Runtime;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Twig\Extension\RuntimeExtensionInterface;

class UserInterfaceRuntime implements RuntimeExtensionInterface
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    public function moduleFooter(): string
    {
        $ref = new ControllerReference('Zikula\ExtensionsModule\Controller\ExtensionsInterfaceController::footer');

        return $this->handler->render($ref) ?? '';
    }

    public function moduleHeader(
        string $type = 'user',
        string $title = '',
        string $titleLink = '',
        bool $setPageTitle = false,
        bool $insertFlashes = false,
        bool $menuFirst = false,
        bool $image = false
    ): string {
        $ref = new ControllerReference('Zikula\ExtensionsModule\Controller\ExtensionsInterfaceController::header', [
            'type' => $type,
            'title' => $title,
            'titlelink' => $titleLink,
            'setpagetitle' => $setPageTitle,
            'insertflashes' => $insertFlashes,
            'menufirst' => $menuFirst,
            'image' => $image
        ]);

        return $this->handler->render($ref) ?? '';
    }

    public function moduleLinks(
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
        $ref = new ControllerReference('Zikula\ExtensionsModule\Controller\ExtensionsInterfaceController::links', [
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

        return $this->handler->render($ref) ?? '';
    }
}
