<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
            ConsoleEvents::COMMAND => array('checkBundleForTranslatingRoutes')
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
