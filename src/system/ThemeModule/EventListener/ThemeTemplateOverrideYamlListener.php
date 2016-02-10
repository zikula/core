<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;
use Zikula\Core\Event\GenericEvent;

/**
 * @deprecated remove at Core-2.0
 * Event handler to override templates.
 */
class ThemeTemplateOverrideYamlListener implements EventSubscriberInterface
{
    /**
     * Associative array.
     *
     * Maps template path to overriden path.
     *
     * @var array
     */
    private $overrideMap = array();

    public function __construct()
    {
        $themeName = \UserUtil::getTheme();
        $theme = \ThemeUtil::getTheme($themeName);
        if (null !== $theme && is_readable($path = $theme->getConfigPath() . '/overrides.yml')) {
            // bundle type theme
            $this->overrideMap = Yaml::parse(file_get_contents($path));
        } elseif (is_readable("themes/$themeName/templates/overrides.yml")) {
            // pre-1.4.0 style theme
            $this->_overrideMap = Yaml::parse(file_get_contents("themes/$themeName/templates/overrides.yml"));
        }
    }

    /**
     * Listens for 'zikula_view.template_override' events.
     *
     * @param GenericEvent $event Event handler.
     *
     * @return void
     */
    public function handler(GenericEvent $event)
    {
        if (array_key_exists($event->data, $this->overrideMap)) {
            $event->data = $this->overrideMap[$event->data];
            $event->stopPropagation();
        }
    }

    public static function getSubscribedEvents()
    {
        // weight as 5 sets theme overrides taking precedent over config overrides
        // @see \Zikula\Bundle\CoreBundle\EventListener\ConfigTemplateOverrideYamlListener
        return array('zikula_view.template_override' => array('handler', 5));
    }
}
