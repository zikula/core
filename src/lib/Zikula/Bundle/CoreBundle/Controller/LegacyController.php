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
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\UnavailableResponse;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * Class LegacyController
 * This controller is a service defined in `CoreBundle/Resources/config/services.xml`
 * @deprecated immediately
 * @package Zikula\Bundle\CoreBundle\Controller
 */
class LegacyController
{
    /**
     * @var VariableApi
     */
    private $variableApi;
    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * MainController constructor.
     * @param VariableApi $variableApi
     */
    public function __construct(VariableApi $variableApi, PermissionApi $permissionApi)
    {
        $this->variableApi = $variableApi;
        $this->permissionApi = $permissionApi;
    }

    /**
     * Controller action to handle modules utilizing legacy shorturls.
     * Route is defined as "/{path}" and therefore collects ALL undefined paths.
     * This route MUST BE LOADED LAST.
     * The route definition is set in `CoreBundle/Resources/config/legacy_routing.xml`
     * @param Request $request
     * @return mixed|Response|PlainResponse
     */
    public function shortUrlAction(Request $request)
    {
        if ($this->variableApi->get(VariableApi::CONFIG, 'shorturls')) {

            \System::resolveLegacyShortUrl($request);

            $module = $request->attributes->get('_zkModule');
            $type = $request->attributes->get('_zkType', 'user');
            $func = $request->attributes->get('_zkFunc', 'index');
            $arguments = $request->attributes->get('_zkArgs');

            // get module information
            $modinfo = \ModUtil::getInfoFromName($module);
            if (!$modinfo) {
                throw new NotFoundHttpException(__('Page not found.'));
            }

            $return = \ModUtil::func($modinfo['name'], $type, $func, $arguments);

            if (false !== $return) {
                if (false === $return instanceof Response) {
                    $response = new Response($return);
                } else {
                    $response = $return;
                }
                $response->legacy = true;

                return $response;
            }
        }
        throw new NotFoundHttpException(_('Page not found.'));
    }

    /**
     * Controller action to handle legacy modules. Does not handle legacy shorturls.
     * Route is defined as "/" with module, type and func guaranteed to not be empty.
     * The route definition is set in `CoreBundle/Resources/config/routing.xml`
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
     * @return bool|mixed|Response|PlainResponse
     */
    public function getLegacyStartPageResponse()
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
        parse_str($args, $arguments);

        return $this->getLegacyResponse($modinfo['name'], $type, $func, $arguments);
    }

    /**
     * Generate a Response object from a legacy module.
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
}