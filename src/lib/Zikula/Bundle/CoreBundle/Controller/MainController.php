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

namespace Zikula\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

/**
 * Main controller.
 */
class MainController
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(ZikulaHttpKernelInterface $kernel, VariableApiInterface $variableApi)
    {
        $this->kernel = $kernel;
        $this->variableApi = $variableApi;
    }

    /**
     * This controller action is designed for the "/" route (home).
     * The route definition is set in `CoreBundle/Resources/config/routing.xml`
     */
    public function homeAction(Request $request): Response
    {
        $controller = $this->variableApi->getSystemVar('startController');
        if (!$controller) {
            return new Response(''); // home page is static
        }
        $args = $this->variableApi->getSystemVar('startargs');
        $attributes = null !== $args ? parse_str($args, $attributes) : [];
        $attributes['_controller'] = $controller;
        $subRequest = $request->duplicate(null, null, $attributes);
        list($moduleName) = explode(':', $controller);

        $subRequest->attributes->set('_zkBundle', $moduleName);
        $subRequest->attributes->set('_zkModule', $moduleName);
        // fix for #3929, #3932
        $request->attributes->set('_zkBundle', $moduleName);
        $request->attributes->set('_zkModule', $moduleName);

        return $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
