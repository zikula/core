<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Zikula\Core\Event\GenericEvent;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event handler to override templates.
 */
class TemplateOverrideYamlListener implements EventSubscriberInterface
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
        if (is_readable('config/template_overrides.yml')) {
            $this->overrideMap = Yaml::parse('config/template_overrides.yml');
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
        return array('zikula_view.template_override' => array('handler', 5));
    }
}