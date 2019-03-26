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

namespace Zikula\ExtensionsModule\Twig\Extension;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleFooterFunction;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleHeaderFunction;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleHelpFunction;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleLinksFunction;

class UserInterfaceExtension extends AbstractExtension
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * constructor.
     * @param FragmentHandler $handler
     */
    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('moduleHeader', [new ModuleHeaderFunction($this->handler), 'display'], ['is_safe' => ['html']]),
            new TwigFunction('moduleLinks', [new ModuleLinksFunction($this->handler), 'display'], ['is_safe' => ['html']]),
            new TwigFunction('moduleHelp', [new ModuleHelpFunction($this->handler), 'display'], ['is_safe' => ['html']]),
            new TwigFunction('moduleFooter', [new ModuleFooterFunction($this->handler), 'display'], ['is_safe' => ['html']]),
        ];
    }
}
