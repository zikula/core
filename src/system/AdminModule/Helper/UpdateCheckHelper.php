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

        $this->checkForUpdates();
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    public function getLastChecked(): int
    {
        return $this->lastChecked;
    }

    public function getCheckInterval(): int
    {
        return $this->checkInterval;
    }

    public function getUpdateversion(): string
    {
        return $this->updateversion;
    }

    public function getReleases()
    {
        return $this->releases;
    }

    public function versionCompare(): int
    {
        return version_compare($this->updateversion, $this->currentVersion);
    }

    public function checkForUpdates(): void
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

        if (true === $this->checked && '' !== $this->updateversion) {
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
     * @return string|bool false if no url handling functions are present or url string
     */
    private function zcurl(string $url, int $timeout = 5)
    {
        $urlArray = parse_url($url);
        $data = '';
        $userAgent = 'Zikula/' . $this->currentVersion;
        $request = $this->requestStack->getMasterRequest();
        $ref = null !== $request ? $request->getBaseURL() : null;
        $port = ('https' === $urlArray['scheme']) ? 443 : 80;

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . '?');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
            if (null !== $ref) {
                curl_setopt($ch, CURLOPT_REFERER, $ref);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if (!ini_get('open_basedir')) {
                // This option does not work with open_basedir set in php.ini
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $data = curl_exec($ch);
            if (!$data && 443 === $port) {
                // retry non ssl
                $url = str_replace('https://', 'http://', $url);
                curl_setopt($ch, CURLOPT_URL, "${url}?");
                $data = curl_exec($ch);
            }
            curl_close($ch);

            return $data;
        }

        if (ini_get('allow_url_fopen')) {
            // handle SSL connections
            $path_query = (isset($urlArray['query']) ? $urlArray['path'] . $urlArray['query'] : $urlArray['path']);
            $host = (443 === $port ? 'ssl://' . $urlArray['host'] : $urlArray['host']);
            $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
            if (!$fp) {
                return false;
            }

            $out = 'GET ' . $path_query . "? HTTP/1.1\r\n";
            $out .= 'User-Agent: ' . $userAgent . "\r\n";
            if (null !== $ref) {
                $out .= 'Referer: ' . $ref . "\r\n";
            }
            $out .= 'Host: ' . $urlArray['host'] . "\r\n";
            $out .= 'Connection: Close' . "\r\n\r\n";
            fwrite($fp, $out);
            while (!feof($fp)) {
                $data .= fgets($fp, 1024);
            }
            fclose($fp);
            $dataArray = explode("\r\n\r\n", $data);

            return $dataArray[1];
        }

        return false;
    }
}
