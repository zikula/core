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

namespace Zikula\ThemeBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ThemeBundle\Engine\AssetBag;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;

class AddJSConfigListener implements EventSubscriberInterface
{
    private bool $installed;

    public function __construct(
        string $installed,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly Environment $twig,
        private readonly AssetBag $footers
    ) {
        $this->installed = '0.0.0' !== $installed;
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
            'baseURL' => $request->getSchemeAndHttpHost() . '/',
            'baseURI' => $request->getBasePath(),
            'lang' => $request->getLocale(),
            'uid' => (int) $this->currentUserApi->get('uid'),
        ];

        $config = array_map('htmlspecialchars', $config);
        $content = $this->twig->render('@ZikulaTheme/Engine/JSConfig.html.twig', [
            'config' => $config,
        ]);
        $this->footers->add([$content => 0]);
    }
}
