<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;
use Zikula\Core\Event\GenericEvent;

/**
 * @deprecated remove at Core-2.0
 * Event handler to override templates
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
    private $overrideMap = [];

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
        if (!is_array($this->overrideMap)) {
            $this->overrideMap = [];
        }
    }

    /**
     * Listens for 'zikula_view.template_override' events.
     *
     * @param GenericEvent $event Event handler
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
        return [
            'zikula_view.template_override' => ['handler', 5]
        ];
    }
}
