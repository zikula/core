<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Class MainController
 * This controller is a service defined in `CoreBundle/Resources/config/services.xml`
 */
class MainController
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * MainController constructor.
     * @param ZikulaHttpKernelInterface $kernelInterface
     * @param VariableApi $variableApi
     */
    public function __construct(ZikulaHttpKernelInterface $kernelInterface, VariableApi $variableApi)
    {
        $this->kernel = $kernelInterface;
        $this->variableApi = $variableApi;
    }

    /**
     * This controller action is designed for the "/" route (home).
     * The route definition is set in `CoreBundle/Resources/config/routing.xml`
     *
     * @param Request $request
     * @return bool|mixed|Response|PlainResponse
     */
    public function homeAction(Request $request)
    {
        $controller = $this->variableApi->getSystemVar('startController');
        if (!$controller) {
            return new Response(''); // home page is static
        }
        $args = $this->variableApi->getSystemVar('startargs');
        parse_str($args, $attributes);
        $attributes['_controller'] = $controller;
        $subRequest = $request->duplicate(null, null, $attributes);

        return $this->kernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
