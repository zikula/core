<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Helper;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

class UpdateCheckHelper
{
    /**
     * @var VariableApiInterface
     */
    protected $variableApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var string
     */
    private $currentVersion;

    /**
     * @var int
     */
    private $lastChecked;

    /**
     * @var int
     */
    private $checkInterval;

    /**
     * @var string
     */
    private $updateversion;

    /**
     * @var bool
     */
    private $force;

    /**
     * @var bool|array
     */
    private $releases;

    /**
     * @var bool
     */
    private $checked;

    /**
     * UpdateCheckHelper constructor.
     *
     * @param VariableApiInterface $variableApi
     * @param RequestStack $requestStack RequestStack service instance
     */
    public function __construct(VariableApiInterface $variableApi, RequestStack $requestStack)
    {
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;

        $this->enabled = (bool)$variableApi->getSystemVar('updatecheck');
        $this->currentVersion = ZikulaKernel::VERSION;
        $this->lastChecked = (int)$variableApi->getSystemVar('updatelastchecked');
        $this->checkInterval = (int)$variableApi->getSystemVar('updatefrequency');
        $this->updateversion = $variableApi->getSystemVar('updateversion');

        $this->force = (bool)$requestStack->getMasterRequest()->query->get('forceupdatecheck');
        $this->checked = false;
        $this->releases = false;
        $this->show = false;

        $this->checkForUpdates();
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    public function getLastChecked()
    {
        return $this->lastChecked;
    }

    public function getCheckInterval()
    {
        return $this->checkInterval;
    }

    public function getUpdateversion()
    {
        return $this->updateversion;
    }

    public function getReleases()
    {
        return $this->releases;
    }

    public function versionCompare()
    {
        return version_compare($this->updateversion, $this->currentVersion);
    }

    public function checkForUpdates()
    {
        $now = time();

        if (false === $this->force && (($now - $this->lastChecked) < ($this->checkInterval * 86400))) {
            // dont get an update because TTL not expired yet
        } else {
            $newVersionInfo = json_decode(trim($this->zcurl('https://api.github.com/repos/zikula/core/releases')), true);
            // Will be set if rate limits encountered
            if (!is_array($newVersionInfo) || isset($newVersionInfo['message'])) {
                $this->releases = false;
            } else {
                $this->releases = $newVersionInfo;
                $this->checked = true;
                foreach ($this->releases as $release) {
                    if (true === $release['prerelease']) {
                        continue;
                    }
                    $this->updateversion = $release['tag_name'];
                    break;
                }
            }
        }

        if ($this->checked === true && $this->updateversion !== '') {
            $this->variableApi->set(VariableApi::CONFIG, 'updatelastchecked', (int)time());
            $this->variableApi->set(VariableApi::CONFIG, 'updateversion', $this->updateversion);
            $this->lastChecked = (int)$this->variableApi->getSystemVar('updatelastchecked');
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
    private function zcurl($url, $timeout = 5)
    {
        $urlArray = parse_url($url);
        $data = '';
        $userAgent = 'Zikula/' . $this->currentVersion;
        $ref = $this->requestStack
            ->getMasterRequest()
            ->getBaseURL();
        $port = (($urlArray['scheme'] == 'https') ? 443 : 80);
        if (function_exists('curl_init')) {
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
        }

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
        }

        return false;
    }
}
