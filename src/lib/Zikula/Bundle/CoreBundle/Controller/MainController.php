<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\UnavailableResponse;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * Class MainController
 * This controller is a service defined in `CoreBundle/Resources/config/services.xml`
 * @package Zikula\Bundle\CoreBundle\Controller
 */
class MainController
{
    /**
     * @var VariableApi
     */
    private $variableApi;
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @deprecated
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * MainController constructor.
     * @param KernelInterface $kernelInterface
     * @param VariableApi $variableApi
     * @param PermissionApi $permissionApi
     */
    public function __construct(KernelInterface $kernelInterface, VariableApi $variableApi, PermissionApi $permissionApi)
    {
        $this->kernel = $kernelInterface;
        $this->variableApi = $variableApi;
        $this->permissionApi = $permissionApi; // @deprecated @todo remove at Core-2.0
    }

    /**
     * This controller action is designed for the "/" route (home).
     * The route definition is set in `CoreBundle/Resources/config/routing.xml`
     * and includes the condition `request.query.get('module') == ''`
     *   which means it will not be used if the query param `module` is set (which is true in legacy urls).
     *
     * @param Request $request
     * @return bool|mixed|Response|PlainResponse
     */
    public function homeAction(Request $request)
    {
        $controller = $this->variableApi->get(VariableApi::CONFIG, 'startController');
        if (!$controller) {
            // @todo remove legacyResponse at Core-2.0
            if (false !== $legacyResponse = $this->getLegacyStartPageResponse()) {
                return $legacyResponse;
            }

            return new Response('');
        }
        $args = $this->variableApi->get(VariableApi::CONFIG, 'startargs');
        $attributes = $this->formatArgumentArray($args);
        $attributes['_controller'] = $controller;
        $subRequest = $request->duplicate(null, null, $attributes);

        return $this->kernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * Controller action to handle legacy modules. Does not handle legacy shorturls.
     * Route is defined as "/" with module, type and func guaranteed to not be empty.
     * The route definition is set in `CoreBundle/Resources/config/routing.xml`
     * @deprecated @todo remove at Core-2.0
     * @param Request $request
     * @return mixed|Response|PlainResponse
     */
    public function legacyAction(Request $request)
    {
        $module = $request->query->get('module');
        $type = $request->query->get('type');
        $func = $request->query->get('func');
        $arguments = $request->query->all();
        unset($arguments['module']);
        unset($arguments['type']);
        unset($arguments['func']);
        $modInfo = \ModUtil::getInfoFromName($module);

        return $this->getLegacyResponse($modInfo['name'], $type, $func, $arguments, $request->isXmlHttpRequest());
    }

    /**
     * Handle homepage legacy response.
     * @deprecated @todo remove at Core-2.0
     * @return bool|mixed|Response|PlainResponse
     */
    private function getLegacyStartPageResponse()
    {
        $module = $this->variableApi->get(VariableApi::CONFIG, 'startpage');
        if (!$module) {
            return false;
        }
        $modinfo = \ModUtil::getInfoFromName($module);
        if (!$modinfo) {
            return false;
        }
        $type = $this->variableApi->get(VariableApi::CONFIG, 'starttype', 'user');
        $func = $this->variableApi->get(VariableApi::CONFIG, 'startfunc', 'index');
        $args = $this->variableApi->get(VariableApi::CONFIG, 'startargs', []);
        $arguments = $this->formatArgumentArray($args);

        return $this->getLegacyResponse($modinfo['name'], $type, $func, $arguments);
    }

    /**
     * Generate a Response object from a legacy module.
     * @deprecated @todo remove at Core-2.0
     * @param $modName
     * @param $type
     * @param $func
     * @param array|null $arguments
     * @param bool $isAjax
     * @return mixed|Response|PlainResponse
     */
    private function getLegacyResponse($modName, $type, $func, array $arguments = null, $isAjax = false)
    {
        if ($isAjax
            && $this->variableApi->get(VariableApi::CONFIG, 'siteoff')
            && !$this->permissionApi->hasPermission('ZikulaSettingsModule::', 'SiteOff::', ACCESS_ADMIN)
            && !($modName == 'ZikulaUsersModule' && $func == 'siteofflogin')) {

            return new UnavailableResponse(__('The site is currently off-line.'));
        }

        $moduleBundle = \ModUtil::getModule($modName);
        if (null !== $moduleBundle) {
            $return = \ModUtil::func($modName, $type, $func);
        } else {
            $return = \ModUtil::func($modName, $type, $func, $arguments);
        }
        if (false === $return) {
            // hack for BC since modules currently use ModUtil::func without expecting exceptions - drak.
            throw new NotFoundHttpException(__('Page not found.'));
        } else {
            if (true === $return) {
                // controllers should not return boolean anymore, this is BC for the time being.
                return new PlainResponse();
            } else {
                if (false === $return instanceof Response) {
                    $response = $isAjax ? new AjaxResponse($return) : new Response($return);
                } else {
                    $response = $return;
                }
            }
            $response->legacy = true;

            return $response;
        }
    }

    /**
     * Convert (string) "arg1=foo,arg2=bar" to array:
     *  ['arg1' => 'foo', 'arg2' => 'bar']
     * @param $args
     * @return array
     */
    private function formatArgumentArray($args)
    {
        $arguments = [];
        foreach (explode(',', $args) as $arg) {
            if (!empty($arg)) {
                $argument = explode('=', $arg);
                $arguments[$argument[0]] = $argument[1];
            }
        }

        return $arguments;
    }
}