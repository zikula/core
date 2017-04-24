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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\ThemeModule\Engine\ParameterBag;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

/**
 * Class AddJSConfigListener
 */
class AddJSConfigListener implements EventSubscriberInterface
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var \Twig_Environment
     */
    private $twig;

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

    /**
     * @var bool
     */
    private $installed;

    /**
     * JSConfig constructor.
     * @param VariableApiInterface $variableApi
     * @param CurrentUserApiInterface $currentUserApi
     * @param \Twig_Environment $twig
     * @param ParameterBag $pageVars
     * @param AssetBag $headers
     * @param string $defaultSessionName
     * @param bool $compat
     */
    public function __construct(
        $installed,
        VariableApiInterface $variableApi,
        CurrentUserApiInterface $currentUserApi,
        \Twig_Environment $twig,
        ParameterBag $pageVars,
        AssetBag $headers,
        $defaultSessionName = '_zsid',
        $compat = false
    ) {
        $this->installed = $installed;
        $this->variableApi = $variableApi;
        $this->currentUserApi = $currentUserApi;
        $this->twig = $twig;
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
            'baseURL' => $event->getRequest()->getSchemeAndHttpHost() . '/',
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
        $content = $this->twig->render('@ZikulaThemeModule/Engine/JSConfig.html.twig', [
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
