<?php
/**
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

class AdminFooterFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * AdminFooterFunction constructor.
     * @param FragmentHandler $handler
     */
    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts admin footer.
     *
     * This has NO configuration options.
     *
     * Examples:
     *
     * <samp>{{ adminFooterFunction() }}</samp>
     *
     * @return string
     */
    public function display()
    {
        $ref = new ControllerReference('ZikulaAdminModule:AdminInterface:footer');

        return $this->handler->render($ref, 'inline', []);
    }
}
