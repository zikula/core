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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

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
     * @var AssetBag
     */
    private $footers;

    /**
     * @var string
     */
    private $defaultSessionName;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        string $installed,
        VariableApiInterface $variableApi,
        CurrentUserApiInterface $currentUserApi,
        Environment $twig,
        AssetBag $footers,
        string $defaultSessionName = '_zsid'
    ) {
        $this->installed = '0.0.0' !== $installed;
        $this->variableApi = $variableApi;
        $this->currentUserApi = $currentUserApi;
        $this->twig = $twig;
        $this->footers = $footers;
        $this->defaultSessionName = $defaultSessionName;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['addJSConfig', -1]
            ]
        ];
    }

    /**
     * Generate a configuration for javascript and add script to site footer.
     */
    public function addJSConfig(ResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (!$this->installed) {
            return;
        }
        $request = $event->getRequest();
        $session = $request->hasSession() ? $request->getSession() : null;

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
        $this->footers->add([$content => 0]);
    }
}
