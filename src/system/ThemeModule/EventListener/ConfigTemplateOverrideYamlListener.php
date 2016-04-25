<?php
/**
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
 * Event handler to override templates.
 */
class ConfigTemplateOverrideYamlListener implements EventSubscriberInterface
{
    /**
     * Associative array.
     *
     * Maps template path to overridden path.
     *
     * @var array
     */
    private $overrideMap = array();

    public function __construct()
    {
        if (is_readable('config/template_overrides.yml')) {
            $this->overrideMap = Yaml::parse(file_get_contents('config/template_overrides.yml'));
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
        // weight as 4 sets theme overrides taking precedent over config overrides
        // @see \Zikula\Bundle\CoreBundle\EventListener\ThemeTemplateOverrideYamlListener
        return array('zikula_view.template_override' => array('handler', 4));
    }
}
