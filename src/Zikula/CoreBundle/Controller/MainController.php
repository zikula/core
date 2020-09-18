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

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        VariableApiInterface $variableApi
    ) {
        $this->kernel = $kernel;
        $this->variableApi = $variableApi;
    }

    /**
     * This controller action is designed for the "/" route (home).
     * The route definition is set in `CoreBundle/Resources/config/routing.xml`
     */
    public function home(Request $request): Response
    {
        $startPageInfo = $this->variableApi->getSystemVar('startController');
        if (!is_array($startPageInfo) || !isset($startPageInfo['controller']) || empty($startPageInfo['controller'])) {
            return new Response(''); // home page is static
        }

        $isValidStartController = true;
        [$route, $controller] = explode('###', $startPageInfo['controller']);
        if (false === mb_strpos($controller, '\\') || false === mb_strpos($controller, '::')) {
            $isValidStartController = false;
        } else {
            [$vendor, $extensionName] = explode('\\', $controller);
            $extensionName = $vendor . $extensionName;
            [$fqcn, $method] = explode('::', $controller);
            if (!$this->kernel->isBundle($extensionName) || !class_exists($fqcn) || !is_callable([$fqcn, $method])) {
                $isValidStartController = false;
            }
        }

        if (!$isValidStartController) {
            return new Response(''); // home page is static
        }

        $queryParams = $requestParams = $attributes = [];
        if (null !== $startPageInfo['query']) {
            parse_str($startPageInfo['query'], $queryParams);
        }
        if (null !== $startPageInfo['attributes']) {
            parse_str($startPageInfo['attributes'], $attributes);
        }
        $attributes['_controller'] = $controller;
        $attributes['_route'] = $route;

        $subRequest = $request->duplicate($queryParams, $requestParams, $attributes);

        $subRequest->attributes->set('_zkBundle', $extensionName);
        $subRequest->attributes->set('_zkModule', $extensionName);
        // fix for #3929, #3932
        $request->attributes->set('_zkBundle', $extensionName);
        $request->attributes->set('_zkModule', $extensionName);

        return $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
