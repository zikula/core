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

namespace Zikula\SettingsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;
use Zikula\ExtensionsModule\ExtensionEvents;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class ModuleListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        VariableApiInterface $variableApi,
        RequestStack $requestStack,
        LocaleApiInterface $localeApi,
        TranslatorInterface $translator
    ) {
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
        $this->localeApi = $localeApi;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExtensionEvents::EXTENSION_DISABLE => ['extensionDeactivated']
        ];
    }

    /**
     * Handle extension deactivated event.
     */
    public function extensionDeactivated(ExtensionStateEvent $event): void
    {
        $extension = $event->getExtension();
        $extensionName = isset($extension) ? $extension->getName() : $event->getInfo()['name'];
        foreach ($this->localeApi->getSupportedLocales() as $lang) {
            $startPageInfo = $this->variableApi->getSystemVar('startController_' . $lang);
            if (!$startPageInfo || !$startPageInfo['controller']) {
                continue;
            }

            $startController = $startPageInfo['controller'];
            if (false === mb_strpos($startController, '\\') || false === mb_strpos($startController, '::')) {
                continue;
            }

            [$vendor, $bundleName] = explode('\\', $startController);
            $bundleName = $vendor . $bundleName;
            if ($bundleName === $extensionName) {
                // since the start extension has been removed, set all related variables to ''
                $this->variableApi->set(VariableApi::CONFIG, 'startController_' . $lang, '');

                $request = $this->requestStack->getCurrentRequest();
                if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
                    $session->getFlashBag()->add(
                        'info',
                        $this->translator->__trans(
                            'The start controller for language "%language%" was reset to a static frontpage.',
                            ['%language%' => $lang]
                        )
                    );
                }
            }
        }
    }
}
