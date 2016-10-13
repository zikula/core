<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\EventListener;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\ThemeModule\Engine\ParameterBag;
use Zikula\UsersModule\Api\CurrentUserApi;

/**
 * Class AddJSConfigListener
 */
class AddJSConfigListener implements EventSubscriberInterface
{
    /**
     * @var VariableApi
     */
    private $variableApi;

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
     * @var AssetBag
     */
    private $headers;

    /**
     * @var string
     */
    private $defaultSessionName;

    /**
     * @var string|bool
     */
    private $compat;

    private $installed;

    /**
     * JSConfig constructor.
     * @param VariableApi $variableApi
     * @param CurrentUserApi $currentUserApi
     * @param EngineInterface $templating
     * @param ParameterBag $pageVars
     * @param AssetBag $headers
     * @param string $defaultSessionName
     * @param bool $compat
     */
    public function __construct(
        $installed,
        VariableApi $variableApi,
        CurrentUserApi $currentUserApi,
        EngineInterface $templating,
        ParameterBag $pageVars,
        AssetBag $headers,
        $defaultSessionName = '_zsid',
        $compat = false
    ) {
        $this->installed = $installed;
        $this->variableApi = $variableApi;
        $this->currentUserApi = $currentUserApi;
        $this->templating = $templating;
        $this->pageVars = $pageVars;
        $this->headers = $headers;
        $this->defaultSessionName = $defaultSessionName;
        $this->compat = $compat;
    }

    /**
     * Generate a configuration for javascript and add script to headers.
     */
    public function addJSConfig(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }
        $session = $event->getRequest()->hasSession() ? $event->getRequest()->getSession() : null;

        $config = [
            'entrypoint' => $this->variableApi->getSystemVar('entrypoint', 'index.php'),
            'baseURL' => $event->getRequest()->getBaseUrl() . '/',
            'baseURI' => $event->getRequest()->getBasePath(),
            'ajaxtimeout' => (int)$this->variableApi->getSystemVar('ajaxtimeout', 5000),
            'lang' => $event->getRequest()->getLocale(),
            'sessionName' => isset($session) ? $session->getName() : $this->defaultSessionName,
            'uid' => (int)$this->currentUserApi->get('uid')
        ];

        $polyfill_features = $this->compat ? \PageUtil::getVar('polyfill_features', []) : []; // @todo remove
        // merge in features added via twig
        $featuresFromTwig = $this->pageVars->get('polyfill_features', []);
        $polyfill_features = array_unique(array_merge($polyfill_features, $featuresFromTwig));

        if (!empty($polyfill_features)) {
            $config['polyfillFeatures'] = implode(' ', $polyfill_features);
        }
        $config = array_map('htmlspecialchars', $config);
        $content = $this->templating->render('@ZikulaThemeModule/Engine/JSConfig.html.twig', [
            'compat' => $this->compat,
            'config' => $config
        ]);
        $this->headers->add([$content => 0]);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['addJSConfig', -1]
            ]
        ];
    }
}
