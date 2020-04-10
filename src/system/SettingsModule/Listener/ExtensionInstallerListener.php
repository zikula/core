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
use Zikula\ExtensionsModule\Event\ExtensionPostCacheRebuildEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostDisabledEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostEnabledEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostRemoveEvent;
use Zikula\ExtensionsModule\Event\ExtensionPostUpgradeEvent;
use Zikula\ExtensionsModule\Event\ExtensionStateEvent;
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
            ExtensionPostCacheRebuildEvent::class => ['updateTranslationConfig', 5],
            ExtensionPostUpgradeEvent::class => ['updateTranslationConfig', 5],
            ExtensionPostEnabledEvent::class => ['updateTranslationConfig', 5],
            ExtensionPostDisabledEvent::class => ['updateTranslationConfig', 5],
            ExtensionPostRemoveEvent::class => ['updateTranslationConfig', 5]
        ];
    }

    public function updateTranslationConfig(ExtensionStateEvent $event): void
    {
        $this->translationConfigHelper->updateConfiguration();
    }
}
