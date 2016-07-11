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

class AdminDeveloperNoticesFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * AdminDeveloperNoticesFunction constructor.
     */
    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts developer notices.
     *
     * This has NO configuration options.
     *
     * Examples:
     *
     * <samp>{{ adminDeveloperNotices() }}</samp>
     *
     * @return string
     */
    public function display()
    {
        $ref = new ControllerReference('ZikulaAdminModule:AdminInterface:developernotices');

        return $this->handler->render($ref, 'inline', []);
    }
}
