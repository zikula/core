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

class ModuleFooterFunction
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
     * Inserts module footer.
     * This has NO configuration options.
     *
     * Example: {( moduleFooter() }}
     */
    public function display(): string
    {
        $ref = new ControllerReference('Zikula\ExtensionsModule\Controller\ExtensionsInterfaceController::footerAction');

        return $this->handler->render($ref);
    }
}
