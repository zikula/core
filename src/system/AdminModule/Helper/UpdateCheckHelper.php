<?php

/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\AdminModule\Helper;

use Zikula\ExtensionsModule\Api\VariableApi;
use Symfony\Component\HttpFoundation\RequestStack;

class UpdateCheckHelper {

    /**
     * @var \Zikula\ExtensionsModule\Api\VariableApi
     */
    protected $variablesManager;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;
    private $enabled;
    private $currentVersion;
    private $lastChecked;
    private $checkInterval;
    private $updateversion;
    private $force;
    private $releases;
    private $checked;

    public function __construct(VariableApi $variablesManager, RequestStack $requestStack) {

        $this->variablesManager = $variablesManager;
        $this->requestStack = $requestStack;

        $this->enabled = (bool) $variablesManager->get('ZConfig', 'updatecheck');
        $this->currentVersion = $variablesManager->get('ZConfig', 'Version_Num');
        $this->lastChecked = (int) $variablesManager->get('ZConfig', 'updatelastchecked');
        $this->checkInterval = (int) $variablesManager->get('ZConfig', 'updatefrequency');
        $this->updateversion = $variablesManager->get('ZConfig', 'updateversion');

        $this->force = (bool) $requestStack->getMasterRequest()->query->get('forceupdatecheck');
        $this->checked = false;
        $this->releases = false;
        $this->show = false;

        $this->checkForUpdates();
    }

    public function getEnabled() {
        return $this->enabled;
    }

    public function getCurrentVersion() {
        return $this->currentVersion;
    }

    public function getLastChecked() {
        return $this->lastChecked;
    }

    public function getCheckInterval() {
        return $this->checkInterval;
    }

    public function getUpdateversion() {
        return $this->updateversion;
    }

    public function getReleases() {
        return $this->releases;
    }

    public function versionCompare() {
        return version_compare($this->updateversion, $this->currentVersion);
    }

    public function checkForUpdates() {

        $now = time();

        if ($this->force == false && (($now - $this->lastChecked) < ($this->checkInterval * 86400))) {
            // dont get an update because TTL not expired yet
        } else {
            $newVersionInfo = json_decode(trim($this->zcurl('https://api.github.com/repos/zikula/core/releases')), true);
            // Will be set if rate limits encountered 
            if (!is_array($newVersionInfo) || isset($newVersionInfo['message'])) {
                $this->releases = false;
            } else {
                $this->releases = $newVersionInfo;
                $this->checked = true;
                $this->updateversion = $this->releases[0]['tag_name'];
                //updateversion - get latest version from releases - move all git releases/updatechecker management to separate class
            }
        }

        if ($this->checked === true && $this->updateversion !== '') {
            $this->variablesManager->set('ZConfig', 'updatelastchecked', (int) time());
            $this->variablesManager->set('ZConfig', 'updateversion', $this->updateversion);
            $this->lastChecked = (int) $this->variablesManager->get('ZConfig', 'updatelastchecked');
        }
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
    private function zcurl($url, $timeout = 5) {
        $urlArray = parse_url($url);
        $data = '';
        $userAgent = 'Zikula/' . $this->currentVersion;
        $ref = $this->requestStack
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
