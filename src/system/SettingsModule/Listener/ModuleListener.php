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

namespace Zikula\SettingsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Event\ExtensionPostDisabledEvent;
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
            ExtensionPostDisabledEvent::class => ['extensionDeactivated']
        ];
    }

    /**
     * Handle extension deactivated event.
     */
    public function extensionDeactivated(ExtensionPostDisabledEvent $event): void
    {
        $extension = $event->getExtensionBundle();
        $deactivatedExtensionName = isset($extension) ? $extension->getName() : $event->getExtensionEntity()->getName();
        $request = $this->requestStack->getCurrentRequest();

        foreach ($this->localeApi->getSupportedLocales() as $lang) {
            $startPageInfo = $this->variableApi->getSystemVar('startController_' . $lang);
            if (!is_array($startPageInfo) || !isset($startPageInfo['controller']) || empty($startPageInfo['controller'])) {
                continue;
            }
            [, $controller] = explode('###', $startPageInfo['controller']);
            if (false === mb_strpos($controller, '\\') || false === mb_strpos($controller, '::')) {
                continue;
            }
            [$vendor, $extensionName] = explode('\\', $controller);
            $extensionName = $vendor . $extensionName;
            if ($extensionName !== $deactivatedExtensionName) {
                continue;
            }

            // since the start extension has been removed, set all related variables to ''
            $this->variableApi->set(VariableApi::CONFIG, 'startController_' . $lang, '');

            if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
                $session->getFlashBag()->add(
                    'info',
                    $this->translator->trans(
                        'The start controller for language "%language%" was reset to a static frontpage.',
                        ['%language%' => $lang]
                    )
                );
            }
        }
    }
}
