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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use RandomLib\Factory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Yaml\Yaml;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

class ParameterHelper
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
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * ParameterHelper constructor.
     */
    public function __construct(
        string $configDir,
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        RequestStack $requestStack,
        ZikulaHttpKernelInterface $kernel
    ) {
        $this->configDir = $configDir;
        $this->variableApi = $variableApi;
        $this->cacheClearer = $cacheClearer;
        $this->requestStack = $requestStack;
        $this->kernel = $kernel;
    }

    public function getYamlHelper(bool $initCopy = false): YamlDumper
    {
        $copyFile = $initCopy ? 'services.yaml' : null;

        return new YamlDumper($this->configDir, 'services_custom.yaml', $copyFile);
    }

    public function initializeParameters(array $paramsToMerge = []): bool
    {
        $yamlHelper = $this->getYamlHelper(true);
        $params = array_merge($yamlHelper->getParameters(), $paramsToMerge);
        if (0 !== mb_strpos($params['database_driver'], 'pdo_')) {
            $params['database_driver'] = 'pdo_' . $params['database_driver']; // doctrine requires prefix in services_custom.yaml
        }
        $yamlHelper->setParameters($params);
        $this->cacheClearer->clear('symfony.config');

        return true;
    }

    /**
     * load and set new default values from the original services.yaml file into the services_custom.yaml file.
     *
     * @return bool
     */
    public function reInitParameters(): bool
    {
        $originalParameters = Yaml::parse(file_get_contents($this->kernel->getProjectDir() . '/config/services.yaml'));
        $yamlHelper = $this->getYamlHelper();
        $yamlHelper->setParameters(array_merge($originalParameters['parameters'], $yamlHelper->getParameters()));
        $this->cacheClearer->clear('symfony.config');

        return true;
    }

    public function finalizeParameters(bool $configureRequestContext = true): bool
    {
        $yamlHelper = $this->getYamlHelper();
        $params = $this->decodeParameters($yamlHelper->getParameters());
        $this->variableApi->getAll(VariableApi::CONFIG); // forces initialization of API
        $this->variableApi->set(VariableApi::CONFIG, 'locale', $params['locale']);
        // Set the System Identifier as a unique string.
        if (!$this->variableApi->get(VariableApi::CONFIG, 'system_identifier')) {
            $this->variableApi->set(VariableApi::CONFIG, 'system_identifier', str_replace('.', '', uniqid((string) (random_int(1000000000, 9999999999)), true)));
        }
        // add admin email as site email
        $this->variableApi->set(VariableApi::CONFIG, 'adminmail', $params['email']);

        // add remaining parameters and remove unneeded ones
        unset($params['username'], $params['password'], $params['email'], $params['dbtabletype']);
        $params['datadir'] = !empty($params['datadir']) ? $params['datadir'] : 'web/uploads';

        $RandomLibFactory = new Factory();
        $generator = $RandomLibFactory->getMediumStrengthGenerator();
        if (!isset($params['secret']) || ('ThisTokenIsNotSoSecretChangeIt' === $params['secret'])) {
            $params['secret'] = $generator->generateString(50);
        }
        if (!isset($params['url_secret'])) {
            $params['url_secret'] = $generator->generateString(10);
        }

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
        $params['installed'] = true;
        // set currently installed version into parameters
        $params[ZikulaKernel::CORE_INSTALLED_VERSION_PARAM] = ZikulaKernel::VERSION;
        // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
        $this->variableApi->set(VariableApi::CONFIG, 'Version_Num', ZikulaKernel::VERSION);

        if (isset($params['upgrading'])) {
            $params['zikula_asset_manager.combine'] = false;
            $startController = $this->variableApi->getSystemVar('startController');
            [$moduleName] = explode(':', $startController);
            if (!$this->kernel->isBundle($moduleName)) {
                // set the 'start' page information to empty to avoid missing module errors.
                $this->variableApi->set(VariableApi::CONFIG, 'startController', '');
                $this->variableApi->set(VariableApi::CONFIG, 'startargs', '');
            }

            // on upgrade, if a user doesn't add their custom theme back to the /theme dir, it should be reset to a core theme, if available.
            $defaultTheme = (string) $this->variableApi->getSystemVar('Default_Theme');
            if (!$this->kernel->isBundle($defaultTheme) && $this->kernel->isBundle('ZikulaBootstrapTheme')) {
                $this->variableApi->set(VariableApi::CONFIG, 'Default_Theme', 'ZikulaBootstrapTheme');
            }
            unset($params['upgrading']);
        }

        $yamlHelper->setParameters($params);

        // clear the cache
        $this->cacheClearer->clear('symfony.config');

        return true;
    }

    public function protectFiles(): bool
    {
        // protect services_custom.yaml files
        $files = array_diff(scandir($this->configDir), ['.', '..']);
        foreach ($files as $file) {
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
