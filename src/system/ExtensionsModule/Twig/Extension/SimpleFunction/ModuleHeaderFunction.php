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

namespace Zikula\ExtensionsModule\Twig\Extension\SimpleFunction;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class ModuleHeaderFunction
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
     * Inserts module header.
     *
     * Example: {{ moduleHeader() }}
     *
     * @param string $type Type of header (defaults to 'user')
     * @param string $title Title to display in header (optional, defaults to module name)
     * @param string $titleLink Link to attach to title (optional, defaults to none)
     * @param bool $setPageTitle If set to true, {{ pageSetVar('title', title) }} is used to set page title
     * @param bool $insertFlashes If set to true, {{ showflashes() }} is put in front of template
     * @param bool $menuFirst If set to true, menu is first, then title
     * @param bool $image If set to true, module image is also displayed next to title
     * @return string
     */
    public function display(
        string $type = 'user',
        string $title = '',
        string $titleLink = '',
        bool $setPageTitle = false,
        bool $insertFlashes = false,
        bool $menuFirst = false,
        bool $image = false
    ): string {
        $ref = new ControllerReference('Zikula\ExtensionsModule\Controller\ExtensionsInterfaceController::headerAction', [
            'type' => $type,
            'title' => $title,
            'titlelink' => $titleLink,
            'setpagetitle' => $setPageTitle,
            'insertflashes' => $insertFlashes,
            'menufirst' => $menuFirst,
            'image' => $image
        ]);

        return $this->handler->render($ref);
    }
}
