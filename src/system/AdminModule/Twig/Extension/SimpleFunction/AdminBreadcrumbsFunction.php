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

namespace Zikula\AdminModule\Twig\Extension\SimpleFunction;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

class AdminBreadcrumbsFunction
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
     * Inserts admin breadcrumbs.
     * This has NO configuration options.
     *
     * Example: {{ adminBreadcrumbs() }}
     */
    public function display(): string
    {
        $ref = new ControllerReference('Zikula\AdminModule\Controller\AdminInterfaceController::breadcrumbsAction');

        return $this->handler->render($ref);
    }
}
