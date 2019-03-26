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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
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
     * @var Environment
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

    private $installed;

    /**
     * JSConfig constructor.
     *
     * @param boolean $installed
     * @param VariableApiInterface $variableApi
     * @param CurrentUserApiInterface $currentUserApi
     * @param Environment $twig
     * @param ParameterBag $pageVars
     * @param AssetBag $headers
     * @param string $defaultSessionName
     */
    public function __construct(
        $installed,
        VariableApiInterface $variableApi,
        CurrentUserApiInterface $currentUserApi,
        Environment $twig,
        ParameterBag $pageVars,
        AssetBag $headers,
        $defaultSessionName = '_zsid'
    ) {
        $this->installed = $installed;
        $this->variableApi = $variableApi;
        $this->currentUserApi = $currentUserApi;
        $this->twig = $twig;
        $this->pageVars = $pageVars;
        $this->headers = $headers;
        $this->defaultSessionName = $defaultSessionName;
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
            'entrypoint' => ZikulaKernel::FRONT_CONTROLLER,
            'baseURL' => $event->getRequest()->getSchemeAndHttpHost() . '/',
            'baseURI' => $event->getRequest()->getBasePath(),
            'ajaxtimeout' => (int)$this->variableApi->getSystemVar('ajaxtimeout', 5000),
            'lang' => $event->getRequest()->getLocale(),
            'sessionName' => isset($session) ? $session->getName() : $this->defaultSessionName,
            'uid' => (int)$this->currentUserApi->get('uid')
        ];

        $config = array_map('htmlspecialchars', $config);
        $content = $this->twig->render('@ZikulaThemeModule/Engine/JSConfig.html.twig', [
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
