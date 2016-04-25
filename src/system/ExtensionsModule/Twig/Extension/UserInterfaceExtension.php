<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Twig\Extension;

use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleHeaderFunction;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleLinksFunction;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleFooterFunction;
use Zikula\ExtensionsModule\Twig\Extension\SimpleFunction\ModuleHelpFunction;

class UserInterfaceExtension extends \Twig_Extension
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

    public function getName()
    {
        return 'zikulaextensionsmodule.admin_interface';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('moduleHeader', [new ModuleHeaderFunction($this->handler), 'display'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('moduleLinks', [new ModuleLinksFunction($this->handler), 'display'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('moduleHelp', [new ModuleHelpFunction($this->handler), 'display'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('moduleFooter', [new ModuleFooterFunction($this->handler), 'display'], ['is_safe' => ['html']]),
        ];
    }
}
