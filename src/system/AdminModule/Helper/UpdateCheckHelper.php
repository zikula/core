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

namespace Zikula\AdminModule\Helper;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

class UpdateCheckHelper
{
    private const URL_RELEASES = 'https://api.github.com/repos/zikula/core/releases';

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

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
            $newVersionInfo = $this->fetchReleases();

            // will be set if rate limits encountered
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

    private function fetchReleases(): array
    {
        $result = [];
        try {
            $client = HttpClient::create();
            $response = $client->request('GET', self::URL_RELEASES);

            $result = $response->toArray();
        } catch (TransportExceptionInterface $exception) {
            // nothing
        }

        return $result;
    }
}
