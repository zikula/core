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
use Zikula\ExtensionsModule\Event\ModuleStateEvent;
use Zikula\ExtensionsModule\ExtensionEvents;
use Zikula\SettingsModule\Helper\TranslationConfigHelper;

/**
 * Event handler base class for extensioninstaller events.
 */
class ExtensionInstallerListener implements EventSubscriberInterface
{
    /**
     * @var TranslationConfigHelper
     */
    private $translationConfigHelper;

    public function __construct(TranslationConfigHelper $translationConfigHelper)
    {
        $this->translationConfigHelper = $translationConfigHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            //ExtensionEvents::MODULE_INSTALL     => ['updateTranslationConfig', 5],
            ExtensionEvents::MODULE_POSTINSTALL => ['updateTranslationConfig', 5],
            ExtensionEvents::MODULE_UPGRADE     => ['updateTranslationConfig', 5],
            ExtensionEvents::MODULE_ENABLE      => ['updateTranslationConfig', 5],
            ExtensionEvents::MODULE_DISABLE     => ['updateTranslationConfig', 5],
            ExtensionEvents::MODULE_REMOVE      => ['updateTranslationConfig', 5]

            // NOTE as there are no events for theme state changes yet,
            // we simply call translationConfigHelper->updateConfiguration
            // in ThemeController at the moment, too
            // refs #3644
        ];
    }

    public function updateTranslationConfig(ModuleStateEvent $event): void
    {
        $this->translationConfigHelper->updateConfiguration();
    }
}
