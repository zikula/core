<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Twig\Extension\SimpleFunction;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class AdminUpdateCheckFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * AdminUpdateCheckFunction constructor.
     * @param FragmentHandler $handler
     */
    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts update check status.
     *
     * This has NO configuration options.
     *
     * Examples:
     *
     * <samp>{{ adminUpdateCheck() }}</samp>
     *
     * @return string
     */
    public function display()
    {
        $ref = new ControllerReference('ZikulaAdminModule:AdminInterface:updatecheck');

        return $this->handler->render($ref, 'inline', []);
    }
}
