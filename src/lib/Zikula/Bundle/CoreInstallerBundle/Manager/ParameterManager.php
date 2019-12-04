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

namespace Zikula\Bundle\CoreInstallerBundle\Manager;

use RandomLib\Factory;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

class ParameterManager
{
    /**
     * @var string
     */
    private $configDir;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * ParameterManager constructor.
     */
    public function __construct(
        string $configDir,
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        RequestStack $requestStack
    ) {
        $this->configDir = $configDir;
        $this->variableApi = $variableApi;
        $this->cacheClearer = $cacheClearer;
        $this->requestStack = $requestStack;
    }

    public function getYamlManager(bool $initCopy = false): YamlDumper
    {
        $copyFile = $initCopy ? 'parameters.yml' : null;

        return new YamlDumper($this->configDir, 'custom_parameters.yml', $copyFile);
    }

    public function initializeParameters(array $paramsToMerge = []): bool
    {
        $yamlManager = $this->getYamlManager(true);
        $params = array_merge($yamlManager->getParameters(), $paramsToMerge);
        if (0 !== mb_strpos($params['database_driver'], 'pdo_')) {
            $params['database_driver'] = 'pdo_' . $params['database_driver']; // doctrine requires prefix in custom_parameters.yml
        }
        $yamlManager->setParameters($params);
        $this->cacheClearer->clear('symfony.config');

        return true;
    }

    public function finalizeParameters(bool $configureRequestContext = true): bool
    {
        $yamlManager = $this->getYamlManager();
        $params = $this->decodeParameters($yamlManager->getParameters());
        $this->variableApi->getAll(VariableApi::CONFIG); // forces initialization of API
        $this->variableApi->set(VariableApi::CONFIG, 'language_i18n', $params['locale']);
        // Set the System Identifier as a unique string.
        $this->variableApi->set(VariableApi::CONFIG, 'system_identifier', str_replace('.', '', uniqid((string) (random_int(1000000000, 9999999999)), true)));
        // add admin email as site email
        $this->variableApi->set(VariableApi::CONFIG, 'adminmail', $params['email']);

        // add remaining parameters and remove unneeded ones
        unset($params['username'], $params['password'], $params['email'], $params['dbtabletype']);
        $params['datadir'] = !empty($params['datadir']) ? $params['datadir'] : 'web/uploads';
        $RandomLibFactory = new Factory();
        $generator = $RandomLibFactory->getMediumStrengthGenerator();
        $params['secret'] = $generator->generateString(50);
        $params['url_secret'] = $generator->generateString(10);
        if ($configureRequestContext) {
            // Configure the Request Context
            // see http://symfony.com/doc/current/cookbook/console/sending_emails.html#configuring-the-request-context-globally
            $request = $this->requestStack->getMasterRequest();
            $hostFromRequest = isset($request) ? $request->getHost() : null;
            $schemeFromRequest = isset($request) ? $request->getScheme() : 'http';
            $basePathFromRequest = isset($request) ? $request->getBasePath() : null;
            $params['router.request_context.host'] = $params['router.request_context.host'] ?? $hostFromRequest;
            $params['router.request_context.scheme'] = $params['router.request_context.scheme'] ?? $schemeFromRequest;
            $params['router.request_context.base_url'] = $params['router.request_context.base_url'] ?? $basePathFromRequest;
        }
        $params['umask'] = $params['umask'] ?? null;
        $yamlManager->setParameters($params);

        // clear the cache
        $this->cacheClearer->clear('symfony.config');

        return true;
    }

    public function protectFiles(): bool
    {
        // set installed = true
        $yamlManager = $this->getYamlManager();
        $params = $yamlManager->getParameters();
        $params['installed'] = true;
        // set currently installed version into parameters
        $params[ZikulaKernel::CORE_INSTALLED_VERSION_PARAM] = ZikulaKernel::VERSION;

        $yamlManager->setParameters($params);

        // protect custom_parameters.yml files
        foreach ([
                     dirname($this->configDir . '/parameters.yml')
                 ] as $file) {
            @chmod($file, 0400);
            if (!is_readable($file)) {
                @chmod($file, 0440);
                if (!is_readable($file)) {
                    @chmod($file, 0444);
                }
            }
        }

        // clear the cache
        $this->cacheClearer->clear('symfony.config');

        return true;
    }

    /**
     * Remove base64 encoding for admin parameters.
     */
    public function decodeParameters(array $params = []): array
    {
        if (!empty($params['password'])) {
            $params['password'] = base64_decode($params['password']);
        }
        if (!empty($params['username'])) {
            $params['username'] = base64_decode($params['username']);
        }
        if (!empty($params['email'])) {
            $params['email'] = base64_decode($params['email']);
        }

        return $params;
    }
}
