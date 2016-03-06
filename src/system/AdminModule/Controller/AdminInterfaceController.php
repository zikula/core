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

namespace Zikula\AdminModule\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zikula\Core\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\Console\Application;

/**
 * @Route("/admininterface")
 */
class AdminInterfaceController extends AbstractController
{
    /**
     * @Route("/header")
     *
     * Open the admin container
     *
     * @return Response symfony response object
     */
    public function headerAction()
    {
        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $caller = [];
        $caller['_zkModule'] = $masterRequest->attributes->get('_zkModule');
        $caller['_zkType'] = $masterRequest->attributes->get('_zkType');
        $caller['_zkFunc'] = $masterRequest->attributes->get('_zkFunc');

        return $this->render("ZikulaAdminModule:AdminInterface:header.html.twig", [
                    'caller' => $caller
        ]);
    }

    /**
     * @Route("/footer")
     *
     * Close the admin container
     *
     * @return Response symfony response object
     */
    public function footerAction()
    {
        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $caller = [];
        $caller['_zkModule'] = $masterRequest->attributes->get('_zkModule');
        $caller['info'] = \ModUtil::getInfoFromName($caller['_zkModule']);

        return $this->render("ZikulaAdminModule:AdminInterface:footer.html.twig", [
                    'caller' => $caller,
                    'symfonyversion' => \Symfony\Component\HttpKernel\Kernel::VERSION,
                    'phpversion' => phpversion()
        ]);
    }

    /**
     * @Route("/breadcrumbs")
     * @Method("GET")
     *
     * Admin breadcrumbs
     *
     * @return Response symfony response object
     */
    public function breadcrumbsAction()
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $requested_cid = $masterRequest->attributes->get('acid');
        $caller = [];
        $caller['_zkModule'] = $masterRequest->attributes->get('_zkModule');
        $caller['_zkType'] = $masterRequest->attributes->get('_zkType');
        $caller['_zkFunc'] = $masterRequest->attributes->get('_zkFunc');
        $caller['info'] = \ModUtil::getInfoFromName($caller['_zkModule']);

        if ($caller['_zkModule'] == 'ZikulaAdminModule') {
            $cid = empty($requested_cid) ? $this->getVar('startcategory') : $requested_cid;
        } else {
            $cid = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory', [
                        'mid' => $caller['info']['id']
            ]);
        }
        $caller['category'] = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', [
                    'cid' => $cid
        ]);

        return $this->render("ZikulaAdminModule:AdminInterface:breadcrumbs.html.twig", [
                    'caller' => $caller
        ]);
    }

    /**
     * @Route("/developernotices")
     *
     * Add developer notices
     *
     * @return Response symfony response object
     */
    public function developernoticesAction()
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $modvars = $this->get('zikula_extensions_module.api.variable')->getAll('ZikulaThemeModule');
        $data = [];
        $data['mode'] = $this->get('kernel')->getEnvironment();
        if ($data['mode'] == 'dev') {
            $data['legacy'] = [
                'status' => true,
                'cssjscombine' => $modvars['cssjscombine'],
                'render' => [
                    'compile_check' => [
                        'state' => $modvars['render_compile_check'],
                        'title' => $this->__('Compile check')
                    ],
                    'force_compile' => [
                        'state' => $modvars['render_force_compile'],
                        'title' => $this->__('Force compile')
                    ],
                    'cache' => [
                        'state' => $modvars['render_cache'],
                        'title' => $this->__('Caching')
                    ]
                ],
                'theme' => [
                    'compile_check' => [
                        'state' => $modvars['compile_check'],
                        'title' => $this->__('Compile check')
                    ],
                    'force_compile' => [
                        'state' => $modvars['force_compile'],
                        'title' => $this->__('Force compile')
                    ],
                    'cache' => [
                        'state' => $modvars['enablecache'],
                        'title' => $this->__('Caching')
                    ]
                ]
            ];
        }

        return $this->render("ZikulaAdminModule:AdminInterface:developernotices.html.twig", [
                    'developer' => $data
        ]);
    }

    /**
     * @Route("/securityanalyzer")
     *
     * Add security analyzer
     *
     * @return Response symfony response object
     */
    public function securityanalyzerAction()
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $data = [];
        $data['scactive'] = (bool) \ModUtil::available('ZikulaSecurityCenterModule');
        // check for outputfilter
        $data['useids'] = (bool) (\ModUtil::available('ZikulaSecurityCenterModule') && $this->get('zikula_extensions_module.api.variable')->get('ZConfig', 'useids') == 1);
        $data['idssoftblock'] = $this->get('zikula_extensions_module.api.variable')->get('ZConfig', 'idssoftblock');

        return $this->render("ZikulaAdminModule:AdminInterface:securityanalyzer.html.twig", [
                    'security' => $data
        ]);
    }

    /**
     * @Route("/updatecheck")
     *
     * Add update check
     *
     * @return Response symfony response object
     */
    public function updatecheckAction()
    {
        if (!$this->get('zikula_extensions_module.api.variable')->get('ZConfig', 'updatecheck')) {
            return [
                'update_show' => false
            ];
        }
        $force = false;

        $now = time();
        $lastChecked = (int) $this->get('zikula_extensions_module.api.variable')->get('ZConfig', 'updatelastchecked');
        $checkInterval = (int) $this->get('zikula_extensions_module.api.variable')->get('ZConfig', 'updatefrequency') * 86400;
        $updateversion = $this->get('zikula_extensions_module.api.variable')->get('ZConfig', 'updateversion');
        $update_show = false;
        $update_version = false;

        if ($force == false && (($now - $lastChecked) < $checkInterval)) {
            // dont get an update because TTL not expired yet
            $onlineVersion = $updateversion;
        } else {
            $this->get('zikula_extensions_module.api.variable')->set('ZConfig', 'updatelastchecked', (int) time());

            $onlineVersion = '';
            $newVersionInfo = trim($this->zcurl('https://api.github.com/repos/zikula/core/releases'));
            if ($newVersionInfo === '') {
                $update_show = false;
            }
            $newVersionInfo = json_decode($newVersionInfo, true);
            if (!is_array($newVersionInfo) || isset($newVersionInfo['message']) /* Will be set if rate limits encountered */) {
                $update_show = false;
            }

            foreach ($newVersionInfo as $version) {
                if (!is_array($version)) {
                    // Invalid response, probably api limits encountered.
                    $update_show = false;
                }
                if (!array_key_exists('prerelease', $version) || $version['prerelease']) {
                    continue;
                }
                if (array_key_exists('tag_name', $version)) {
                    if (version_compare($version['tag_name'], $onlineVersion) == 1) {
                        $onlineVersion = $version['tag_name'];
                    }
                }
            }

            if ($onlineVersion == '') {
                $update_show = false;
            }
            $this->get('zikula_extensions_module.api.variable')->set('ZConfig', 'updateversion', $onlineVersion);
        }

        // compare with db Version_Num
        // if 1 then there is a later version available
        if (version_compare($onlineVersion, $this->get('zikula_extensions_module.api.variable')->get('ZConfig', 'Version_Num') == 1)) {
            $update_show = true;
            $update_version = $onlineVersion;
        } else {
            $update_show = false;
        }

        return $this->render("ZikulaAdminModule:AdminInterface:updatecheck.html.twig", [
                    'update_show' => $update_show,
                    'update_version' => $update_version
        ]);
    }

    /**
     * @Route("/menu")
     *
     * Admin menu.
     *
     * @param string $mode
     *            string $template
     *
     * @return Response symfony response object
     */
    public function menuAction()
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $masterRequest = $this->get('request_stack')->getMasterRequest();
        $currentRequest = $this->get('request_stack')->getCurrentRequest();
        // get caller info
        $caller = [];
        $caller['_zkModule'] = $masterRequest->attributes->get('_zkModule');
        $caller['_zkType'] = $masterRequest->attributes->get('_zkType');
        $caller['_zkFunc'] = $masterRequest->attributes->get('_zkFunc');
        $caller['path'] = $masterRequest->getPathInfo();
        $caller['info'] = \ModUtil::getInfoFromName($caller['_zkModule']);
        // category we are in
        $requestedCid = $masterRequest->attributes->get('acid');
        if ($caller['_zkModule'] == 'ZikulaAdminModule' || $caller['_zkModule'] == '') {
            $cid = empty($requestedCid) ? $this->getVar('startcategory') : $requestedCid;
        } else {
            $cid = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory', [
                        'mid' => $caller['info']['id']
            ]);
        }
        $caller['category'] = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', [
                    'cid' => $cid
        ]);
        // mode requested
        $mode = (null !== $currentRequest->attributes->get('mode')) ? $currentRequest->attributes->get('mode') : 'categories';
        // template requested
        $template = (null !== $currentRequest->attributes->get('template')) ? $currentRequest->attributes->get('template') : 'tabs';
        // get admin capable modules
        $adminModules = \ModUtil::getModulesCapableOf('admin');
        // sort modules by displayname
        $moduleNames = [];
        foreach ($adminModules as $key => $module) {
            $moduleNames[$key] = $module['displayname'];
        }
        array_multisort($moduleNames, SORT_ASC, $adminModules);
        $menuModules = [];
        $menuCategories = [];
        foreach ($adminModules as $adminModule) {
            if ($this->hasPermission("$adminModule[name]::", '::', ACCESS_EDIT)) {
                // cat
                $catid = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory', [
                            'mid' => $adminModule['id']
                ]);
                $category = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getCategory', [
                            'cid' => $catid
                ]);
                // order
                $order = \ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getSortOrder', [
                            'mid' => \ModUtil::getIdFromName($adminModule['name'])
                ]);
                // url
                $menutexturl = isset($adminModule['capabilities']['admin']['url']) ? $adminModule['capabilities']['admin']['url'] : $this->get('router')->generate($adminModule['capabilities']['admin']['route']);
                // text's
                $menutext = $adminModule['displayname'];
                $menutexttitle = $adminModule['description'];

                $links = ($this->get('zikula.link_container_collector')->getLinks($adminModule['name'], 'admin') == false)
                        ? (array) \ModUtil::apiFunc($adminModule['name'], 'admin', 'getLinks')
                        : $this->get('zikula.link_container_collector')->getLinks($adminModule['name'], 'admin')
                        ;

                $module = array(
                    'menutexturl' => $menutexturl,
                    'menutext' => $menutext,
                    'menutexttitle' => $menutexttitle,
                    'modname' => $adminModule['name'],
                    'order' => $order,
                    'id' => $adminModule['id'],
                    'links' => $links,
                    'icon' => \ModUtil::getModuleImagePath($adminModule['name'])
                );

                $menuModules[$adminModule['name']] = $module;
                // category menu
                $menuCategories[$catid]['title'] = $category['name'];
                $menuCategories[$catid]['url'] = $this->get('router')->generate('zikulaadminmodule_admin_adminpanel', [
                    'acid' => $category['cid']
                ]);
                $menuCategories[$catid]['description'] = $category['description'];
                $menuCategories[$catid]['cid'] = $category['cid'];
                $menuCategories[$catid]['modules'][$adminModule['name']] = $module;
            }
        }
        $fullTemplateName = $mode . '.' . $template;

        return $this->render("ZikulaAdminModule:AdminInterface:$fullTemplateName.html.twig", [
                    'adminMenu' => ('categories' == $mode) ? $menuCategories : $menuModules,
                    'mode' => $mode,
                    'caller' => $caller
        ]);
    }

    /**
     * Zikula curl
     *
     * This function is internal for the time being and may be extended to be a proper library
     * or find an alternative solution later.
     *
     * @param string $url
     * @param int $timeout
     *            default=5
     *
     * @return string|bool false if no url handling functions are present or url string
     */
    private function zcurl($url, $timeout = 5)
    {
        $urlArray = parse_url($url);
        $data = '';
        $userAgent = 'Zikula/' . $this->get('zikula_extensions_module.api.variable')->get('ZConfig', 'Version_Num');
        $ref = $this->get('request_stack')
                ->getMasterRequest()
                ->getBaseURL();
        $port = (($urlArray['scheme'] == 'https') ? 443 : 80);
        if (ini_get('allow_url_fopen')) {
            // handle SSL connections
            $path_query = (isset($urlArray['query']) ? $urlArray['path'] . $urlArray['query'] : $urlArray['path']);
            $host = ($port == 443 ? "ssl://$urlArray[host]" : $urlArray['host']);
            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if (!$fp) {
                return false;
            } else {
                $out = "GET $path_query? HTTP/1.1\r\n";
                $out .= "User-Agent: $userAgent\r\n";
                $out .= "Referer: $ref\r\n";
                $out .= "Host: $urlArray[host]\r\n";
                $out .= "Connection: Close\r\n\r\n";
                fwrite($fp, $out);
                while (!feof($fp)) {
                    $data .= fgets($fp, 1024);
                }
                fclose($fp);
                $dataArray = explode("\r\n\r\n", $data);

                return $dataArray[1];
            }
        } elseif (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_URL, "$url?");
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($ch, CURLOPT_REFERER, $ref);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
                // This option doesnt work in safe_mode or with open_basedir set in php.ini
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $data = curl_exec($ch);
            if (!$data && $port = 443) {
                // retry non ssl
                $url = str_replace('https://', 'http://', $url);
                curl_setopt($ch, CURLOPT_URL, "$url?");
                $data = @curl_exec($ch);
            }
            //$headers = curl_getinfo($ch);
            curl_close($ch);

            return $data;
        } else {
            return false;
        }
    }
}
