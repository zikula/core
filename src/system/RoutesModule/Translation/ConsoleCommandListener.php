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

namespace Zikula\RoutesModule\Translation;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\RoutesModule\Helper\ExtractTranslationHelper;

class ConsoleCommandListener implements EventSubscriberInterface
{
    /**
     * @var ExtractTranslationHelper
     */
    private $extractTranslationHelper;

    public function __construct(ExtractTranslationHelper $extractTranslationHelper)
    {
        $this->extractTranslationHelper = $extractTranslationHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['checkBundleForTranslatingRoutes']
        ];
    }

    /**
     * This function saves the bundle whose routes shall be translated in a global variable to be used in
     * Zikula\RoutesModule\Translation\DefaultRouteExclusionStrategy later on.
     */
    public function checkBundleForTranslatingRoutes(ConsoleCommandEvent $event): void
    {
        if ('translation:extract' !== $event->getCommand()->getName()) {
            return;
        }

        $this->extractTranslationHelper->setBundleName('');

        if ($event->getInput()->hasParameterOption('--bundle')) {
            $bundle = $event->getInput()->getParameterOption('--bundle');
            if ('@' === $bundle[0]) {
                $bundle = substr($bundle, 1);
            }

            $this->extractTranslationHelper->setBundleName($bundle);
        }
    }
}
