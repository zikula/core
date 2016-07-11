<?php

/*
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

class ModuleFooterFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * ModuleFooterFunction constructor.
     * @param FragmentHandler $handler
     */
    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts module footer.
     *
     * This has NO configuration options.
     *
     * Examples:
     *
     * <samp>{( moduleFooter() }}</samp>
     *
     * @return string
     */
    public function display()
    {
        $ref = new ControllerReference('ZikulaExtensionsModule:ExtensionsInterface:footer');

        return $this->handler->render($ref, 'inline', []);
    }
}
