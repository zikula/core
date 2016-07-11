<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\RoutesModule\Translation;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Class ConsoleCommandListener.
 */
class ConsoleCommandListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array (
            ConsoleEvents::COMMAND => ['checkBundleForTranslatingRoutes']
        );
    }

    /**
     * This function saves the bundle whose routes shall be translated in a global variable to be used in
     * Zikula\RoutesModule\Translation\DefaultRouteExclusionStrategy later on.
     *
     * @param ConsoleCommandEvent $event
     */
    public function checkBundleForTranslatingRoutes(ConsoleCommandEvent $event)
    {
        if ($event->getCommand()->getName() !== 'translation:extract') {
            return;
        }

        $GLOBALS['translation_extract_routes'] = true;

        if ($event->getInput()->hasParameterOption('--bundle')) {
            $bundle = $event->getInput()->getParameterOption('--bundle');
            if ('@' === $bundle[0]) {
                $bundle = substr($bundle, 1);
            }

            $GLOBALS['translation_extract_routes_bundle'] = $bundle;
        }
    }
}
