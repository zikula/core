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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use RandomLib\Factory;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Helper\LocalDotEnvHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

class ParameterHelper
{
    /**
     * @var string
     */
    private $configDir;

    /**
     * @var string
     */
    private $projectDir;

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

    private $encodedParameterNames = [
        'password',
        'username',
        'email',
        'transport',
        'mailer_id',
        'mailer_key',
        'host',
        'port',
        'customParameters',
        'enableLogging'
    ];

    /**
     * ParameterHelper constructor.
     */
    public function __construct(
        string $projectDir,
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        RequestStack $requestStack
    ) {
        $this->configDir = $projectDir . '/config';
        $this->projectDir = $projectDir;
        $this->variableApi = $variableApi;
        $this->cacheClearer = $cacheClearer;
        $this->requestStack = $requestStack;
    }

    public function getYamlHelper(): YamlDumper
    {
        return new YamlDumper($this->configDir, 'temp_params.yaml');
    }

    public function initializeParameters(array $paramsToMerge = []): bool
    {
        $yamlHelper = $this->getYamlHelper();
        $params = array_merge($yamlHelper->getParameters(), $paramsToMerge);
        $yamlHelper->setParameters($params);
        $this->cacheClearer->clear('symfony.config');

        return true;
    }

    /**
     * @throws IOExceptionInterface If .env.local could not be dumped
     */
    public function finalizeParameters(): bool
    {
        $yamlHelper = $this->getYamlHelper();
        $params = $this->decodeParameters($yamlHelper->getParameters());

        $this->variableApi->getAll(VariableApi::CONFIG); // forces initialization of API
        if (!isset($params['upgrading']) || !$params['upgrading']) {
            $this->variableApi->set(VariableApi::CONFIG, 'locale', $params['locale']);
            // Set the System Identifier as a unique string.
            if (!$this->variableApi->get(VariableApi::CONFIG, 'system_identifier')) {
                $this->variableApi->set(VariableApi::CONFIG, 'system_identifier', str_replace('.', '', uniqid((string) (random_int(1000000000, 9999999999)), true)));
            }
            // add admin email as site email
            $this->variableApi->set(VariableApi::CONFIG, 'adminmail', $params['email']);
            $this->setMailerData($params);
            $this->configureRequestContext($params);
        }

        $params = array_diff_key($params, array_flip($this->encodedParameterNames)); // remove all encoded params

        // store the recent version in a config var for later usage. This enables us to determine the version we are upgrading from
        $this->variableApi->set(VariableApi::CONFIG, 'Version_Num', ZikulaKernel::VERSION);

        $this->writeEnvVars($params);

        $yamlHelper->deleteFile();

        // clear the cache
        $this->cacheClearer->clear('symfony.config');

        return true;
    }

    /**
     * Configure the Request Context
     * see https://symfony.com/doc/current/routing.html#generating-urls-in-commands
     * This is needed because emails are sent from CLI requiring routes to be built
     */
    private function configureRequestContext(array &$params): void
    {
        $request = $this->requestStack->getMasterRequest();
        $hostFromRequest = isset($request) ? $request->getHost() : 'localhost';
        $schemeFromRequest = isset($request) ? $request->getScheme() : 'http';
        $basePathFromRequest = isset($request) ? $request->getBasePath() : null;
        $params['router.request_context.host'] = $params['router.request_context.host'] ?? $hostFromRequest;
        $params['router.request_context.scheme'] = $params['router.request_context.scheme'] ?? $schemeFromRequest;
        $params['router.request_context.base_url'] = $params['router.request_context.base_url'] ?? $basePathFromRequest;
    }

    /**
     * @param array $params values from upgrade
     */
    private function writeEnvVars(array &$params): void
    {
        $randomLibFactory = new Factory();
        $generator = $randomLibFactory->getMediumStrengthGenerator();
        $secret = isset($params['secret']) && !empty($params['secret']) && '%env(APP_SECRET)%' !== $params['secret']
            ? $params['secret']
            : $generator->generateString(50)
        ;
        $vars = [
            'APP_ENV' => $params['env'] ?? 'prod',
            'APP_DEBUG' => isset($params['debug']) ? (int) ($params['debug']) : 0,
            'APP_SECRET' => '!\'' . $secret . '\'',
            'ZIKULA_INSTALLED' => '\'' . ZikulaKernel::VERSION . '\''
        ];
        if (isset($params['router.request_context.host'])) {
            $vars['DEFAULT_URI'] = sprintf('!%s://%s%s', $params['router.request_context.scheme'], $params['router.request_context.host'], $params['router.request_context.base_url']);
            unset($params['router.request_context.scheme'], $params['router.request_context.host'], $params['router.request_context.base_url']);
        }
        (new LocalDotEnvHelper($this->projectDir))->writeLocalEnvVars($vars);
    }

    /**
     * Write params to file as encoded values.
     *
     * @throws AbortStageException
     */
    public function writeEncodedParameters(array $data): void
    {
        $yamlHelper = $this->getYamlHelper();
        foreach ($data as $k => $v) {
            $data[$k] = is_string($v) ? base64_encode($v) : $v; // encode so values are 'safe' for json
        }
        $params = array_merge($yamlHelper->getParameters(), $data);
        try {
            $yamlHelper->setParameters($params);
        } catch (IOException $exception) {
            throw new AbortStageException(sprintf('Cannot write parameters to %s file.', 'services_custom.yaml'));
        }
    }

    /**
     * Remove base64 encoding for parameters.
     */
    public function decodeParameters(array $params = []): array
    {
        foreach ($this->encodedParameterNames as $parameterName) {
            if (!empty($params[$parameterName])) {
                $params[$parameterName] = is_string($params[$parameterName]) ? base64_decode($params[$parameterName]) : $params[$parameterName];
            }
        }

        return $params;
    }

    private function setMailerData(array $params): void
    {
        // params have already been decoded
        $mailerParams = array_intersect_key($params, array_flip($this->encodedParameterNames));
        unset($mailerParams['mailer_key'], $mailerParams['password'], $mailerParams['username'], $mailerParams['email']);
        $this->variableApi->setAll('ZikulaMailerModule', $mailerParams);
    }
}
