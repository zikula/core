<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Api\CurrentUserApi;

class JSConfig
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CurrentUserApi
     */
    private $currentUserApi;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var ParameterBag
     */
    private $pageVars;

    /**
     * @var string|bool
     */
    private $compat;

    /**
     * JSConfig constructor.
     * @param VariableApi $variableApi
     * @param RequestStack $requestStack
     * @param CurrentUserApi $currentUserApi
     * @param EngineInterface $templating
     * @param ParameterBag $pageVars
     * @param bool $compat
     */
    public function __construct(
        VariableApi $variableApi,
        RequestStack $requestStack,
        CurrentUserApi $currentUserApi,
        EngineInterface $templating,
        ParameterBag $pageVars,
        $compat = false
    ) {
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
        $this->currentUserApi = $currentUserApi;
        $this->templating = $templating;
        $this->pageVars = $pageVars;
        $this->compat = $compat;
    }

    /**
     * Generate a configuration for javascript and return script tag to embed in HTML HEAD.
     *
     * @return string HTML code with script tag
     */
    public function generate()
    {
        $config = [
            'entrypoint' => $this->variableApi->get(VariableApi::CONFIG, 'entrypoint', 'index.php'),
            'baseURL' => $this->requestStack->getMasterRequest()->getBaseUrl(),
            'baseURI' => $this->requestStack->getMasterRequest()->getBasePath(),
            'ajaxtimeout' => (int)$this->variableApi->get(VariableApi::CONFIG, 'ajaxtimeout', 5000),
            'lang' => $this->requestStack->getMasterRequest()->getLocale(),
            'sessionName' => $this->requestStack->getMasterRequest()->getSession()->getName(),
            'uid' => (int)$this->currentUserApi->get('uid')
        ];

        $polyfill_features = $this->compat ? \PageUtil::getVar('polyfill_features', []) : []; // @todo remove
        // merge in features added via twig
        $featuresFromTwig = $this->pageVars->get('polyfill_features', []);
        $polyfill_features = array_unique(array_merge($polyfill_features, $featuresFromTwig));

        if (!empty($polyfill_features)) {
            $config['polyfillFeatures'] = implode(' ', $polyfill_features);
        }

        return $this->templating->render('@ZikulaThemeModule/Engine/JSConfig.js.twig', [
            'compat' => $this->compat,
            'config' => $config
        ]);
    }
}
