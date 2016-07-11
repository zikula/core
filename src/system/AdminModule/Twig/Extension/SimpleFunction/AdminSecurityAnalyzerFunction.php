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

class AdminSecurityAnalyzerFunction
{
    /**
     * @var FragmentHandler
     */
    private $handler;

    /**
     * AdminSecurityAnalyzerFunction constructor.
     * @param FragmentHandler $handler
     */
    public function __construct(FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Inserts security analyzer informations.
     *
     * This has NO configuration options.
     *
     * Examples:
     *
     * <samp>{{ adminSecurityAnalyzer() }}</samp>
     *
     * @return string
     */
    public function display()
    {
        $ref = new ControllerReference('ZikulaAdminModule:AdminInterface:securityanalyzer');

        return $this->handler->render($ref, 'inline', []);
    }
}
